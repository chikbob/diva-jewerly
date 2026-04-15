<?php

namespace App\Payments\Gateways;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;

class CashOnDeliveryGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'cash_on_delivery';
    }

    public function initiate(Order $order): array
    {
        return [
            'provider' => $this->key(),
            'payment_method' => $this->key(),
            'provider_reference' => null,
            'status' => 'pending',
            'checkout_url' => null,
            'provider_payload' => [
                'instructions' => 'Collect payment when the order is delivered.',
                'provider_status' => 'pending',
            ],
        ];
    }

    public function supportsWebhooks(): bool
    {
        return false;
    }

    public function verifyWebhookSignature(string $rawPayload, ?string $signature): bool
    {
        return false;
    }

    public function parseWebhook(string $rawPayload): array
    {
        throw new \LogicException('Cash on delivery does not support webhooks.');
    }

    public function reconcile(PaymentTransaction $transaction): array
    {
        return [
            'provider_reference' => $transaction->provider_reference,
            'status' => 'pending',
            'provider_payload' => $transaction->provider_payload ?? [],
        ];
    }
}
