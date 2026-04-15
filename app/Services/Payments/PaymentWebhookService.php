<?php

namespace App\Services\Payments;

use App\Models\PaymentTransaction;
use App\Support\AuditLogger;
use App\Support\MetricStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentWebhookService
{
    public function __construct(
        private readonly PaymentManager $paymentManager,
        private readonly PaymentOrderSynchronizer $synchronizer,
        private readonly MetricStore $metricStore,
    ) {
    }

    public function handle(string $provider, string $rawPayload, ?string $signature): PaymentTransaction
    {
        $gateway = $this->paymentManager->gatewayForProvider($provider);

        if (! $gateway->supportsWebhooks()) {
            throw new HttpException(404, 'Payment provider does not accept webhooks.');
        }

        if (! $gateway->verifyWebhookSignature($rawPayload, $signature)) {
            $this->metricStore->incrementCounter(
                'payment_webhooks_total',
                'Incoming payment webhooks by provider and outcome.',
                ['provider' => $provider, 'outcome' => 'invalid_signature']
            );

            throw new HttpException(401, 'Invalid payment webhook signature.');
        }

        $payload = $gateway->parseWebhook($rawPayload);
        $reference = (string) ($payload['reference'] ?? '');

        if ($reference === '') {
            throw ValidationException::withMessages([
                'reference' => 'Payment reference is required.',
            ]);
        }

        $transaction = DB::transaction(function () use ($provider, $payload, $reference): PaymentTransaction {
            /** @var PaymentTransaction $transaction */
            $transaction = PaymentTransaction::query()
                ->with('order')
                ->where('provider', $provider)
                ->where('reference', $reference)
                ->lockForUpdate()
                ->firstOrFail();

            $providerPayload = is_array($payload['provider_payload'] ?? null)
                ? $payload['provider_payload']
                : [];

            $status = (string) ($payload['status'] ?? 'pending');

            $transaction->forceFill([
                'provider_reference' => $payload['provider_reference'] ?? $transaction->provider_reference,
                'status' => $status,
                'provider_payload' => array_merge($transaction->provider_payload ?? [], $providerPayload, [
                    'provider_status' => $status,
                ]),
                'last_webhook_at' => now(),
                'paid_at' => $status === 'paid' ? ($transaction->paid_at ?? now()) : null,
                'failed_at' => $status === 'failed' ? now() : null,
            ])->save();

            $this->synchronizer->sync($transaction->order, $transaction);

            AuditLogger::info('payment.webhook.processed', [
                'order_id' => $transaction->order_id,
                'payment_reference' => $transaction->reference,
                'payment_provider' => $provider,
                'payment_status' => $transaction->status,
                'payment_provider_reference' => $transaction->provider_reference,
            ]);

            return $transaction->fresh(['order', 'order.items.product']);
        });

        $this->metricStore->incrementCounter(
            'payment_webhooks_total',
            'Incoming payment webhooks by provider and outcome.',
            ['provider' => $provider, 'outcome' => 'processed']
        );

        return $transaction;
    }
}
