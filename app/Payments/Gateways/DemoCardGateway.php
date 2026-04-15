<?php

namespace App\Payments\Gateways;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Payments\Contracts\PaymentGateway;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class DemoCardGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'demo_card';
    }

    public function initiate(Order $order): array
    {
        return [
            'provider' => $this->key(),
            'payment_method' => $this->key(),
            'provider_reference' => 'DEMO-' . Str::upper(Str::random(16)),
            'status' => 'pending',
            'checkout_url' => URL::route('payments.show', ['paymentReference' => $order->payment_reference]),
            'provider_payload' => [
                'instructions' => 'Confirm the demo payment from the hosted payment status screen or via webhook.',
                'provider_status' => 'pending',
            ],
        ];
    }

    public function supportsWebhooks(): bool
    {
        return true;
    }

    public function verifyWebhookSignature(string $rawPayload, ?string $signature): bool
    {
        if ($signature === null || $signature === '') {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $rawPayload, (string) config('payments.providers.demo_card.webhook_secret')),
            $signature
        );
    }

    public function parseWebhook(string $rawPayload): array
    {
        $payload = json_decode($rawPayload, true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid demo card webhook payload.');
        }

        return [
            'reference' => (string) ($payload['reference'] ?? ''),
            'provider_reference' => $payload['provider_reference'] ?? null,
            'status' => $this->normalizeStatus((string) ($payload['status'] ?? 'pending')),
            'provider_payload' => $payload,
        ];
    }

    public function reconcile(PaymentTransaction $transaction): array
    {
        $providerPayload = $transaction->provider_payload ?? [];

        return [
            'provider_reference' => $transaction->provider_reference,
            'status' => $this->normalizeStatus((string) ($providerPayload['provider_status'] ?? $transaction->status)),
            'provider_payload' => $providerPayload,
        ];
    }

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'paid', 'success', 'captured' => 'paid',
            'failed', 'declined' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            default => 'pending',
        };
    }
}
