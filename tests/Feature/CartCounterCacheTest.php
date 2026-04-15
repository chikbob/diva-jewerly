<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\CartCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCounterCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_counter_cache_is_invalidated_after_add_and_remove(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertSame(0, CartCounter::countFor($user));

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $this->assertSame(1, CartCounter::countFor($user->fresh()));

        $this->actingAs($user)->post(route('cart.remove'), [
            'product_id' => $product->id,
        ]);

        $this->assertSame(0, CartCounter::countFor($user->fresh()));
    }

    public function test_cart_counter_cache_is_invalidated_after_checkout(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertSame(2, CartCounter::countFor($user->fresh()));

        $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'payment_method' => 'demo_card',
        ]);

        $this->assertSame(0, CartCounter::countFor($user->fresh()));
    }
}
