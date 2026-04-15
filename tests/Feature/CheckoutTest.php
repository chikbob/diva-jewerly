<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_an_order_without_storing_card_data(): void
    {
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

        $response->assertRedirect('/');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'full_name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'payment_method' => 'demo_card',
            'status' => 'paid',
            'total' => 3999.98,
        ]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('cart_items', 0);
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
