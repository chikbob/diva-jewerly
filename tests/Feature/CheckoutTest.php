<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_a_pending_demo_payment_and_redirects_to_payment_status(): void
    {
        Log::spy();

        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 1999.99,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'full_name' => 'Test Buyer',
                'email' => 'buyer@example.com',
                'payment_method' => 'demo_card',
            ]);

        $order = \App\Models\Order::query()->firstOrFail();

        $response->assertRedirect(route('payments.show', [
            'paymentReference' => $order->payment_reference,
        ]));
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'full_name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'payment_method' => 'demo_card',
            'payment_provider' => 'demo_card',
            'payment_status' => 'pending',
            'status' => 'pending',
            'total' => 3999.98,
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'demo_card',
            'reference' => $order->payment_reference,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('cart_items', 0);
        Log::shouldHaveReceived('info')->withArgs(
            fn (string $message, array $context): bool => $message === 'checkout.order.created'
                && $context['auth_user_id'] === $user->getAuthIdentifier()
                && $context['item_count'] === 2
        )->once();
    }

    public function test_cash_on_delivery_checkout_redirects_home_with_pending_payment(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 500,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('checkout.store'), [
                'full_name' => 'COD Buyer',
                'email' => 'cod@example.com',
                'payment_method' => 'cash_on_delivery',
            ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_method' => 'cash_on_delivery',
            'payment_provider' => 'cash_on_delivery',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_method' => 'cash_on_delivery',
            'provider' => 'cash_on_delivery',
            'status' => 'pending',
        ]);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'full_name' => 'Test Buyer',
                'email' => 'buyer@example.com',
                'payment_method' => 'cash_on_delivery',
            ]);

        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }
}
