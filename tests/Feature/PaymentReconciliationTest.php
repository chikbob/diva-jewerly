<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconciliation_command_syncs_paid_provider_state_back_to_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_method' => 'demo_card',
            'payment_provider' => 'demo_card',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        PaymentTransaction::query()->create([
            'order_id' => $order->id,
            'provider' => 'demo_card',
            'payment_method' => 'demo_card',
            'reference' => $order->payment_reference,
            'provider_reference' => 'DEMO-REC-1234',
            'amount' => $order->total,
            'currency' => 'UAH',
            'status' => 'paid',
            'provider_payload' => [
                'provider_status' => 'paid',
            ],
            'paid_at' => now(),
        ]);

        Artisan::call('payments:reconcile', [
            '--provider' => 'demo_card',
            '--reference' => $order->payment_reference,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'reference' => $order->payment_reference,
            'status' => 'paid',
        ]);
        $this->assertNotNull($order->fresh()->payment_reconciled_at);
    }
}
