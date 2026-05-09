<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderIndexRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Support\CartCounter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(OrderIndexRequest $request): \Inertia\Response
    {
        $filters = $request->filters();

        $query = $this->userOrderQuery($request)
            ->when(
                ! empty($filters['status']),
                static fn ($builder) => $builder->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['payment_status']),
                static fn ($builder) => $builder->where('payment_status', $filters['payment_status'])
            );

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total')->orderByDesc('created_at'),
            'total_desc' => $query->orderByDesc('total')->orderByDesc('created_at'),
            default => $query->latest(),
        };

        $orders = $query->get()->map(fn (Order $order): array => $this->presentOrder($order));

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statusOptions' => [
                ['value' => '', 'label' => 'Усі статуси'],
                ['value' => 'pending', 'label' => 'В очікуванні'],
                ['value' => 'paid', 'label' => 'Сплачено'],
                ['value' => 'failed', 'label' => 'Помилка оплати'],
                ['value' => 'cancelled', 'label' => 'Скасовано'],
            ],
            'paymentStatusOptions' => [
                ['value' => '', 'label' => 'Будь-який статус оплати'],
                ['value' => 'pending', 'label' => 'Оплата очікується'],
                ['value' => 'paid', 'label' => 'Оплачено'],
                ['value' => 'failed', 'label' => 'Оплата неуспішна'],
                ['value' => 'cancelled', 'label' => 'Оплату скасовано'],
            ],
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Спочатку нові'],
                ['value' => 'oldest', 'label' => 'Спочатку старі'],
                ['value' => 'total_desc', 'label' => 'Сума: за спаданням'],
                ['value' => 'total_asc', 'label' => 'Сума: за зростанням'],
            ],
        ]);
    }

    public function show(Request $request, Order $order): \Inertia\Response
    {
        $order = $this->userOrderQuery($request)
            ->whereKey($order->id)
            ->firstOrFail();

        return Inertia::render('Orders/Show', [
            'order' => $this->presentOrder($order),
        ]);
    }

    public function repeat(Request $request, Order $order): RedirectResponse
    {
        $order = $this->userOrderQuery($request, ['items'])
            ->whereKey($order->id)
            ->firstOrFail();

        if ($order->items->isEmpty()) {
            return redirect()
                ->route('orders.index')
                ->with('error', 'У замовленні немає товарів для повторного додавання.');
        }

        DB::transaction(function () use ($order, $request): void {
            foreach ($order->items as $item) {
                $cartItem = CartItem::query()
                    ->where('user_id', $request->user()->id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($cartItem !== null) {
                    $cartItem->update([
                        'quantity' => $cartItem->quantity + $item->quantity,
                    ]);

                    continue;
                }

                CartItem::query()->create([
                    'user_id' => $request->user()->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }
        });

        CartCounter::forgetForUserId($request->user()->id);

        return redirect()
            ->route('cart.index')
            ->with('message', 'Товари з замовлення повторно додано до кошика.');
    }

    private function userOrderQuery(Request $request, array $with = ['items.product.category', 'paymentTransaction']): Builder
    {
        return Order::query()
            ->with($with)
            ->where('user_id', $request->user()->id);
    }

    private function presentOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'full_name' => $order->full_name,
            'email' => $order->email,
            'payment_method' => $order->payment_method,
            'payment_provider' => $order->payment_provider,
            'payment_reference' => $order->payment_reference,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'total' => $order->total,
            'items' => $order->items->map(function ($item): array {
                $product = $item->product;

                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'line_total' => (float) $item->price * $item->quantity,
                    'product_name' => $product?->name ?? 'Товар більше недоступний',
                    'product_description' => $product?->description,
                    'product_image' => $product?->image_path,
                    'product_category' => $product?->category?->name,
                    'product' => $product === null ? null : [
                        'id' => $product->id,
                        'name' => $product->name,
                    ],
                ];
            })->values()->all(),
        ];
    }
}
