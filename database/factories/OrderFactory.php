<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'payment_method' => fake()->randomElement(['demo_card', 'cash_on_delivery']),
            'payment_provider' => fake()->randomElement(['demo_card', 'cash_on_delivery']),
            'payment_reference' => 'DIVA-' . strtoupper(fake()->bothify('############')),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'total' => fake()->randomFloat(2, 100, 10000),
            'status' => fake()->randomElement(['pending', 'paid', 'failed']),
        ];
    }
}
