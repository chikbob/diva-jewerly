<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Support\AdminPanel\AdminResourceRegistry;
use App\Support\BackofficeAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminResourceRegistry $registry,
        private readonly BackofficeAccess $backofficeAccess,
    ) {}

    public function __invoke(Request $request): Response
    {
        $user = Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();

        $stats = [
            ['label' => 'Товари', 'value' => Product::query()->count(), 'accent' => 'rose'],
            ['label' => 'Категорії', 'value' => Category::query()->count(), 'accent' => 'gold'],
            ['label' => 'Клієнти', 'value' => User::query()->count(), 'accent' => 'slate'],
            ['label' => 'Замовлення', 'value' => Order::query()->count(), 'accent' => 'emerald'],
            ['label' => 'Платежі', 'value' => PaymentTransaction::query()->count(), 'accent' => 'violet'],
            ['label' => 'Обране', 'value' => Favorite::query()->count(), 'accent' => 'amber'],
        ];

        $latestOrders = Order::query()
            ->latest('created_at')
            ->limit(6)
            ->get(['id', 'full_name', 'payment_reference', 'status', 'payment_status', 'total', 'created_at'])
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'full_name' => $order->full_name,
                'payment_reference' => $order->payment_reference,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total' => $order->total,
                'created_at' => $order->created_at?->format('d.m.Y H:i'),
            ])->all();

        return Inertia::render('Admin/Dashboard', [
            'navigation' => $this->navigationFor($user),
            'stats' => $stats,
            'latestOrders' => $latestOrders,
        ]);
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
}
