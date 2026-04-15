<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Anton Diva',
            'email' => 'anton@gmail.com',
            'password' => bcrypt('1234567890'),
        ]);

        $categorySeeds = [
            [
                'name' => 'Каблучки',
                'description' => 'Вишукані каблучки для щоденного образу та особливих подій.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
            ],
            [
                'name' => 'Сережки',
                'description' => 'Класичні та сучасні сережки з акцентом на стиль і легкість.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
            ],
            [
                'name' => 'Підвіски',
                'description' => 'Акуратні підвіски, що підкреслюють індивідуальність.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
            ],
            [
                'name' => 'Браслети',
                'description' => 'Браслети для повсякденного носіння та святкових образів.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
            ],
            [
                'name' => 'Кольє',
                'description' => 'Елегантні кольє для завершення ювелірного комплекту.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
            ],
        ];

        $categories = collect($categorySeeds)->map(
            fn (array $category) => Category::create($category)
        );

        $categories->each(function ($category) {
            Product::factory()->count(10)->create([
                'category_id' => $category->id,
            ]);
        });

        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => 'Anton Diva',
            'email' => $user->email,
            'payment_method' => 'demo_card',
            'payment_reference' => 'DIVA-SEED-000001',
            'total' => 0,
            'status' => 'paid',
        ]);

        $products = Product::inRandomOrder()->take(3)->get();
        $total = 0;

        foreach ($products as $product) {
            $qty = rand(1, 3);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $product->price,
            ]);
            $total += $product->price * $qty;
        }

        $order->update(['total' => $total]);
    }
}
