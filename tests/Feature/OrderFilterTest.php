<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_can_be_filtered_by_status_payment_status_and_sorted_by_total(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => 1000,
        ]);

        $target = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'paid',
            'total' => 2500,
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'failed',
            'total' => 500,
        ]);

        $this->actingAs($user)
            ->get(route('orders.index', [
                'status' => 'paid',
                'payment_status' => 'paid',
                'sort' => 'total_desc',
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->where('filters.status', 'paid')
                ->where('filters.payment_status', 'paid')
                ->where('filters.sort', 'total_desc')
                ->has('orders', 1)
                ->where('orders.0.id', $target->id)
            );
    }

    public function test_user_can_repeat_order_into_cart_and_merge_quantities(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('orders.repeat', $order));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_user_can_open_their_order_details(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Golden Ring',
        ]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_reference' => 'DIVA-ORDER-123',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1500,
        ]);

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Show')
                ->where('order.id', $order->id)
                ->where('order.payment_reference', 'DIVA-ORDER-123')
                ->has('order.items', 1)
                ->where('order.items.0.product_name', 'Golden Ring')
                ->where('order.items.0.line_total', 3000)
            );
    }

    public function test_user_cannot_open_someone_elses_order_details(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create();

        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }
}
