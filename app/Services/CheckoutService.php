<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\CartCounter;
use App\Support\MetricStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutService
{
    public function __construct(private readonly MetricStore $metricStore)
    {
    }

    public function createOrderFromCart(User $user, array $payload): Order
    {
        $startedAt = microtime(true);
        $paymentMethod = (string) ($payload['payment_method'] ?? 'unknown');

        try {
            $order = DB::transaction(function () use ($user, $payload): Order {
                $items = CartItem::query()
                    ->where('user_id', $user->id)
                    ->with('product')
                    ->lockForUpdate()
                    ->get();

                $this->guardAgainstInvalidCart($items);

                $order = Order::create([
                    'user_id' => $user->id,
                    'full_name' => $payload['full_name'],
                    'email' => $payload['email'],
                    'payment_method' => $payload['payment_method'],
                    'payment_reference' => $this->generatePaymentReference(),
                    'total' => $items->sum(fn (CartItem $item) => $item->product->price * $item->quantity),
                    'status' => $payload['payment_method'] === 'cash_on_delivery' ? 'pending' : 'paid',
                ]);

                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                    ]);
                }

                CartItem::query()
                    ->where('user_id', $user->id)
                    ->delete();
                CartCounter::forgetForUserId($user->id);
                AuditLogger::info('checkout.order.created', [
                    'auth_user_id' => $user->getAuthIdentifier(),
                    'order_id' => $order->getKey(),
                    'item_count' => $items->sum('quantity'),
                    'total' => $order->total,
                    'payment_method' => $order->payment_method,
                    'payment_reference' => $order->payment_reference,
                ]);

                return $order->load('items.product');
            });

            $this->recordCheckoutMetrics('success', $paymentMethod, $startedAt);

            return $order;
        } catch (Throwable $exception) {
            $this->recordCheckoutMetrics('failure', $paymentMethod, $startedAt);

            throw $exception;
        }
    }

    private function guardAgainstInvalidCart(Collection $items): void
    {
        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        if ($items->contains(fn (CartItem $item) => $item->product === null)) {
            throw ValidationException::withMessages([
                'cart' => 'One or more products in your cart are no longer available.',
            ]);
        }
    }

    private function generatePaymentReference(): string
    {
        do {
            $reference = sprintf('DIVA-%s', Str::upper(Str::random(12)));
        } while (Order::query()->where('payment_reference', $reference)->exists());

        return $reference;
    }

    private function recordCheckoutMetrics(string $outcome, string $paymentMethod, float $startedAt): void
    {
        $labels = [
            'outcome' => $outcome,
            'payment_method' => $paymentMethod,
        ];

        $this->metricStore->incrementCounter(
            'checkout_orders_total',
            'Checkout attempts by outcome and payment method.',
            $labels
        );

        $this->metricStore->observeHistogram(
            'checkout_duration_seconds',
            'Checkout processing duration in seconds by outcome and payment method.',
            microtime(true) - $startedAt,
            $labels,
            config('operations.metrics.checkout.duration_buckets')
        );
    }
}
