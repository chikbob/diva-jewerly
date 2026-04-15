<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_card_webhook_marks_payment_as_paid(): void
    {
        [$order, $transaction] = $this->createPendingDemoPayment();

        $payload = json_encode([
            'reference' => $order->payment_reference,
            'provider_reference' => $transaction->provider_reference,
            'status' => 'paid',
        ], JSON_THROW_ON_ERROR);

        $response = $this->withHeader(
            'X-Diva-Signature',
            hash_hmac('sha256', $payload, (string) config('payments.providers.demo_card.webhook_secret'))
        )->postJson(route('payments.webhooks.handle', ['provider' => 'demo_card']), json_decode($payload, true, 512, JSON_THROW_ON_ERROR));

        $response
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('reference', $order->payment_reference)
            ->assertJsonPath('status', 'paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);
    }

    public function test_demo_card_webhook_rejects_invalid_signature(): void
    {
        [$order] = $this->createPendingDemoPayment();

        $response = $this->withHeader('X-Diva-Signature', 'invalid-signature')
            ->postJson(route('payments.webhooks.handle', ['provider' => 'demo_card']), [
                'reference' => $order->payment_reference,
                'status' => 'paid',
            ]);

        $response->assertUnauthorized();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }

    public function test_demo_payment_screen_uses_same_lifecycle_to_mark_payment_failed(): void
    {
        $user = User::factory()->create();
        [$order] = $this->createPendingDemoPayment($user);

        $response = $this->actingAs($user)->post(route('payments.simulate', [
            'paymentReference' => $order->payment_reference,
            'status' => 'failed',
        ]));

        $response->assertRedirect(route('payments.show', [
            'paymentReference' => $order->payment_reference,
        ]));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'failed',
            'status' => 'failed',
        ]);
    }

    private function createPendingDemoPayment(?User $user = null): array
    {
        $user ??= User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'demo_card',
            'payment_provider' => 'demo_card',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $transaction = PaymentTransaction::query()->create([
            'order_id' => $order->id,
            'provider' => 'demo_card',
            'payment_method' => 'demo_card',
            'reference' => $order->payment_reference,
            'provider_reference' => 'DEMO-TEST-1234',
            'amount' => $order->total,
            'currency' => 'UAH',
            'status' => 'pending',
            'provider_payload' => [
                'provider_status' => 'pending',
            ],
        ]);

        return [$order, $transaction];
    }
}
