<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_can_be_filtered_by_status_and_sorted_by_total(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 1000,
        ]);

        $target = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total' => 2500,
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'total' => 500,
        ]);

        $this->actingAs($user)
            ->get(route('orders.index', [
                'status' => 'paid',
                'sort' => 'total_desc',
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Orders/Index')
                ->where('filters.status', 'paid')
                ->where('filters.sort', 'total_desc')
                ->has('orders', 2)
                ->where('orders.0.id', $target->id)
            );
    }
}
