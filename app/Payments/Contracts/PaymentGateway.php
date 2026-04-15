<?php

namespace App\Payments\Contracts;

use App\Models\Order;
use App\Models\PaymentTransaction;

interface PaymentGateway
{
    public function key(): string;

    public function initiate(Order $order): array;

    public function supportsWebhooks(): bool;

    public function verifyWebhookSignature(string $rawPayload, ?string $signature): bool;

    public function parseWebhook(string $rawPayload): array;

    public function reconcile(PaymentTransaction $transaction): array;
}
