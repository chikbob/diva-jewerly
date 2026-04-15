<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\CartCounter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function createOrderFromCart(User $user, array $payload): Order
    {
        return DB::transaction(function () use ($user, $payload): Order {
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
}
