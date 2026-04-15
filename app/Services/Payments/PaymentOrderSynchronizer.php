<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;

class PaymentOrderSynchronizer
{
    public function sync(Order $order, PaymentTransaction $transaction, ?Carbon $reconciledAt = null): Order
    {
        $status = $this->normalizeStatus($transaction->status);

        $attributes = [
            'payment_provider' => $transaction->provider,
            'payment_status' => $status,
            'payment_reconciled_at' => $reconciledAt,
        ];

        if ($status === 'paid') {
            $attributes['status'] = 'paid';
            $attributes['paid_at'] = $order->paid_at ?? $transaction->paid_at ?? now();
        } elseif ($status === 'failed') {
            $attributes['status'] = 'failed';
            $attributes['paid_at'] = null;
        } elseif ($status === 'cancelled') {
            $attributes['status'] = 'cancelled';
            $attributes['paid_at'] = null;
        } else {
            $attributes['status'] = 'pending';
            $attributes['paid_at'] = null;
        }

        $order->forceFill($attributes)->save();

        return $order->fresh(['items.product', 'paymentTransaction']);
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, ['paid', 'failed', 'cancelled'], true)
            ? $status
            : 'pending';
    }
}
