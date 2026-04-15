<?php

namespace App\Services\Payments;

use App\Models\PaymentTransaction;
use App\Support\AuditLogger;
use App\Support\MetricStore;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationService
{
    public function __construct(
        private readonly PaymentManager $paymentManager,
        private readonly PaymentOrderSynchronizer $synchronizer,
        private readonly MetricStore $metricStore,
    ) {
    }

    public function reconcile(?string $provider = null, ?string $reference = null): array
    {
        $transactions = PaymentTransaction::query()
            ->with('order')
            ->when($provider !== null && $provider !== '', fn ($query) => $query->where('provider', $provider))
            ->when($reference !== null && $reference !== '', fn ($query) => $query->where('reference', $reference))
            ->whereIn('status', ['pending', 'paid', 'failed'])
            ->get();

        $updated = 0;

        /** @var PaymentTransaction $transaction */
        foreach ($transactions as $transaction) {
            $gateway = $this->paymentManager->gatewayForProvider($transaction->provider);
            $snapshot = $gateway->reconcile($transaction);
            $nextStatus = (string) ($snapshot['status'] ?? $transaction->status);

            DB::transaction(function () use ($transaction, $snapshot, $nextStatus, &$updated): void {
                $fresh = PaymentTransaction::query()
                    ->with('order')
                    ->lockForUpdate()
                    ->findOrFail($transaction->id);

                $payload = is_array($snapshot['provider_payload'] ?? null) ? $snapshot['provider_payload'] : [];
                $changed = $fresh->status !== $nextStatus;

                $fresh->forceFill([
                    'provider_reference' => $snapshot['provider_reference'] ?? $fresh->provider_reference,
                    'status' => $nextStatus,
                    'provider_payload' => array_merge($fresh->provider_payload ?? [], $payload, [
                        'provider_status' => $nextStatus,
                    ]),
                    'reconciled_at' => now(),
                    'paid_at' => $nextStatus === 'paid' ? ($fresh->paid_at ?? now()) : null,
                    'failed_at' => $nextStatus === 'failed' ? ($fresh->failed_at ?? now()) : null,
                ])->save();

                $this->synchronizer->sync($fresh->order, $fresh, now());

                if ($changed) {
                    $updated++;
                }

                AuditLogger::info('payment.reconciliation.processed', [
                    'order_id' => $fresh->order_id,
                    'payment_reference' => $fresh->reference,
                    'payment_provider' => $fresh->provider,
                    'payment_status' => $fresh->status,
                    'status_changed' => $changed,
                ]);
            });
        }

        $this->metricStore->incrementCounter(
            'payment_reconciliations_total',
            'Payment reconciliation runs by outcome.',
            ['outcome' => 'completed']
        );

        return [
            'checked' => $transactions->count(),
            'updated' => $updated,
        ];
    }
}
