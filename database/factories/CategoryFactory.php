<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
        ];
    }
}
