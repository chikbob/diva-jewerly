<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_same_product_is_merged_into_a_single_cart_row(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $this->actingAs($user)->post(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(2, CartItem::query()->firstOrFail()->quantity);
    }

    public function test_a_product_can_be_removed_from_the_cart(): void
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

        $response = $this->actingAs($user)->post(route('cart.remove'), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('cart_items', 0);
    }
}
