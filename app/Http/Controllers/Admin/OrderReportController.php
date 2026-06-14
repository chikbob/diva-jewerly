<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\AdminPanel\AdminResourceRegistry;
use App\Support\BackofficeAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderReportController extends Controller
{
    public function __construct(
        private readonly AdminResourceRegistry $registry,
        private readonly BackofficeAccess $backofficeAccess,
    ) {}

    public function index(Request $request): Response
    {
        $user = Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();

        abort_unless($this->backofficeAccess->canAccessAdminDomain($user, 'operations', 'viewAny'), 403);

        $filters = $this->filters($request);
        $query = $this->query($filters);

        $orders = (clone $query)
            ->with(['items.product'])
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Order $order): array => [
                'id' => $order->id,
                'created_at' => $order->created_at?->format('d.m.Y H:i'),
                'full_name' => $order->full_name,
                'email' => $order->email,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'total' => (float) $order->total,
                'items_count' => $order->items->count(),
                'quantity_total' => $order->items->sum('quantity'),
            ]);

        $summary = (clone $query)
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue_total')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw('COALESCE(AVG(total), 0) as average_total')
            ->first();

        $topProducts = OrderItem::query()
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('order_items.order_id', (clone $query)->select('orders.id'))
            ->groupBy('order_items.product_id', 'products.name')
            ->selectRaw('order_items.product_id')
            ->selectRaw("COALESCE(products.name, 'Товар недоступний') as product_name")
            ->selectRaw('SUM(order_items.quantity) as quantity_sold')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as revenue_total')
            ->orderByDesc('quantity_sold')
            ->limit(10)
            ->get()
            ->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity_sold' => (int) $item->quantity_sold,
                'revenue_total' => (float) $item->revenue_total,
            ])
            ->all();

        $dailyBreakdown = (clone $query)
            ->selectRaw('DATE(created_at) as report_date')
            ->selectRaw('COUNT(*) as orders_count')
            ->selectRaw('SUM(total) as revenue_total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('report_date')
            ->limit(14)
            ->get()
            ->map(fn ($row): array => [
                'date' => $row->report_date,
                'orders_count' => (int) $row->orders_count,
                'revenue_total' => (float) $row->revenue_total,
            ])
            ->all();

        return Inertia::render('Admin/Reports/Orders', [
            'navigation' => $this->navigationFor($user),
            'filters' => $filters,
            'filterOptions' => [
                'statuses' => $this->statusOptions(),
                'paymentStatuses' => $this->statusOptions(),
            ],
            'summary' => [
                'orders_count' => (int) ($summary?->orders_count ?? 0),
                'revenue_total' => (float) ($summary?->revenue_total ?? 0),
                'paid_count' => (int) ($summary?->paid_count ?? 0),
                'average_total' => (float) ($summary?->average_total ?? 0),
            ],
            'orders' => $orders,
            'topProducts' => $topProducts,
            'dailyBreakdown' => $dailyBreakdown,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();

        abort_unless($this->backofficeAccess->canAccessAdminDomain($user, 'operations', 'viewAny'), 403);

        $filters = $this->filters($request);
        $orders = $this->query($filters)
            ->with('items')
            ->latest('created_at')
            ->get();

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Created At',
                'Customer',
                'Email',
                'Status',
                'Payment Status',
                'Payment Method',
                'Items',
                'Quantity',
                'Total',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->created_at?->format('Y-m-d H:i:s'),
                    $order->full_name,
                    $order->email,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->items->count(),
                    $order->items->sum('quantity'),
                    $order->total,
                ]);
            }

            fclose($handle);
        }, 'orders-report.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function query(array $filters): Builder
    {
        return Order::query()
            ->when(
                $filters['date_from'] !== '',
                fn (Builder $builder) => $builder->whereDate('created_at', '>=', $filters['date_from'])
            )
            ->when(
                $filters['date_to'] !== '',
                fn (Builder $builder) => $builder->whereDate('created_at', '<=', $filters['date_to'])
            )
            ->when(
                $filters['status'] !== '',
                fn (Builder $builder) => $builder->where('status', $filters['status'])
            )
            ->when(
                $filters['payment_status'] !== '',
                fn (Builder $builder) => $builder->where('payment_status', $filters['payment_status'])
            );
    }

    private function filters(Request $request): array
    {
        return [
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
            'status' => trim((string) $request->input('status', '')),
            'payment_status' => trim((string) $request->input('payment_status', '')),
        ];
    }

    private function navigationFor($user): array
    {
        return collect($this->registry->navigation())->map(function (array $group) use ($user): array {
            return [
                'label' => $group['label'],
                'items' => collect($group['items'])
                    ->filter(fn (array $item): bool => $this->backofficeAccess->canAccessAdminDomain($user, $item['domain'], 'viewAny'))
                    ->values()
                    ->all(),
            ];
        })->filter(fn (array $group): bool => $group['items'] !== [])->values()->all();
    }

    private function statusOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'В очікуванні'],
            ['value' => 'paid', 'label' => 'Сплачено'],
            ['value' => 'failed', 'label' => 'Помилка'],
            ['value' => 'cancelled', 'label' => 'Скасовано'],
        ];
    }
}
