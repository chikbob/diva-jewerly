<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoUser = User::query()->updateOrCreate([
            'email' => 'user@diva.local',
        ], [
            'name' => 'Demo Olena Kovalchuk',
            'password' => Hash::make('user12345'),
            'email_verified_at' => now(),
        ]);

        $adminRole = MoonshineUserRole::query()->firstOrCreate([
            'name' => 'Admin',
        ]);

        MoonshineUser::query()->updateOrCreate([
            'email' => 'admin@diva.local',
        ], [
            'moonshine_user_role_id' => $adminRole->id,
            'name' => 'Demo Iryna Levchenko',
            'password' => Hash::make('admin12345'),
        ]);

        $showcaseUsers = collect([
            [
                'name' => 'Demo Maryna Havryliuk',
                'email' => 'marina.demo@diva.local',
            ],
            [
                'name' => 'Demo Sofiia Melnyk',
                'email' => 'sofia.demo@diva.local',
            ],
            [
                'name' => 'Demo Nataliia Romaniuk',
                'email' => 'natalia.demo@diva.local',
            ],
            [
                'name' => 'Demo Kateryna Shevchuk',
                'email' => 'kateryna.demo@diva.local',
            ],
        ])->map(fn (array $user): User => User::query()->updateOrCreate(
            ['email' => $user['email']],
            [
                'name' => $user['name'],
                'password' => Hash::make('user12345'),
                'email_verified_at' => now(),
            ]
        ));

        $categorySeeds = [
            [
                'name' => 'Rings',
                'description' => 'Refined rings for everyday looks and special occasions.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Demo Ring Rose Glow', 'description' => 'Gold-plated ring with a pink crystal for a delicate everyday accent.', 'price' => 2680.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 4],
                    ['name' => 'Demo Ring Emerald Promise', 'description' => 'Ring with a green stone and a row of cubic zirconia in a modern setting.', 'price' => 7191.91, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 10],
                    ['name' => 'Demo Ring Golden Lace', 'description' => 'Fine openwork design made for stacking with other jewelry.', 'price' => 3190.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 38],
                    ['name' => 'Demo Ring Pearl Line', 'description' => 'Minimal ring with a pearl accent for polished office styling.', 'price' => 4120.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 22],
                ],
            ],
            [
                'name' => 'Earrings',
                'description' => 'Classic and contemporary earrings focused on style and lightness.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Earrings Demo Blush Drop', 'description' => 'Long earrings with a soft pink glow for an evening look.', 'price' => 3580.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 7],
                    ['name' => 'Earrings Demo Crystal Hoop', 'description' => 'Compact hoop earrings with stone detailing for daily wear.', 'price' => 2890.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 14],
                    ['name' => 'Earrings Demo Ivory Spark', 'description' => 'A soft design with a pale stone that fits a versatile wardrobe.', 'price' => 4310.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 45],
                    ['name' => 'Earrings Demo Velvet Shine', 'description' => 'Minimal pair with a soft golden shine and delicate geometry.', 'price' => 3985.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 3],
                ],
            ],
            [
                'name' => 'Pendants',
                'description' => 'Clean pendant silhouettes that underline personal style.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Demo Pendant Moon Charm', 'description' => 'Romantic pendant with smooth metal surfaces and a clean silhouette.', 'price' => 3907.51, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 12],
                    ['name' => 'Demo Pendant Love Script', 'description' => 'Light pendant with a defined contour and gift-ready mood.', 'price' => 2470.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 29],
                    ['name' => 'Demo Pendant Aurora Star', 'description' => 'Bright accent with a cool-shine stone on a fine chain.', 'price' => 4510.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 61],
                    ['name' => 'Demo Pendant Signature Heart', 'description' => 'Commercial best-seller with a soft heart silhouette.', 'price' => 3325.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 5],
                ],
            ],
            [
                'name' => 'Bracelets',
                'description' => 'Bracelets for everyday wear and elevated occasion looks.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Demo Bracelet Silk Chain', 'description' => 'Light chain bracelet that pairs well with a watch.', 'price' => 4850.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 8],
                    ['name' => 'Demo Bracelet Tender Pearl', 'description' => 'Design with a pearl accent for understated feminine styling.', 'price' => 5290.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 18],
                    ['name' => 'Demo Bracelet Milano Shine', 'description' => 'Textured weave with a rich shine suited for gift collections.', 'price' => 6120.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 41],
                    ['name' => 'Demo Bracelet Daily Grace', 'description' => 'Comfortable everyday bracelet with an easy fit.', 'price' => 2760.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 2],
                ],
            ],
            [
                'name' => 'Necklaces',
                'description' => 'Elegant necklaces to complete a jewelry set.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Necklace Demo Soft Rose', 'description' => 'Rosy tone and a soft line create a premium commercial feel.', 'price' => 6890.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 9],
                    ['name' => 'Necklace Demo Evening Pearl', 'description' => 'Elegant necklace designed for dresses with an open neckline.', 'price' => 7420.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 27],
                    ['name' => 'Necklace Demo Riviera Light', 'description' => 'Occasion-focused design with vivid shine and an even drape.', 'price' => 9150.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 55],
                    ['name' => 'Necklace Demo City Minimal', 'description' => 'Minimal necklace for a modern essential wardrobe.', 'price' => 4380.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 1],
                ],
            ],
        ];

        $categories = collect($categorySeeds)->map(
            fn (array $category) => Category::query()->updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'image_url' => $category['image_url'],
                ]
            )
        );

        $products = collect();

        $categories->each(function (Category $category) use (&$products, $categorySeeds): void {
            $seed = collect($categorySeeds)->firstWhere('name', $category->name);

            foreach ($seed['products'] as $productSeed) {
                $product = Product::query()->updateOrCreate(
                    ['name' => $productSeed['name']],
                    [
                        'category_id' => $category->id,
                        'description' => $productSeed['description'],
                        'price' => $productSeed['price'],
                        'image_path' => $productSeed['image_path'],
                    ]
                );

                $product->forceFill([
                    'created_at' => now()->subDays($productSeed['days_ago']),
                    'updated_at' => now()->subDays(max($productSeed['days_ago'] - 1, 0)),
                ])->saveQuietly();

                $products->push($product->fresh());
            }
        });

        $productMap = $products->keyBy('name');

        $favoriteProducts = [
            'Demo Ring Rose Glow',
            'Earrings Demo Velvet Shine',
            'Demo Pendant Signature Heart',
            'Necklace Demo Soft Rose',
        ];

        foreach ($favoriteProducts as $productName) {
            Favorite::query()->updateOrCreate([
                'user_id' => $demoUser->id,
                'product_id' => $productMap[$productName]->id,
            ]);
        }

        foreach ([
            ['name' => 'Demo Ring Emerald Promise', 'quantity' => 1],
            ['name' => 'Demo Bracelet Daily Grace', 'quantity' => 2],
        ] as $cartItem) {
            CartItem::query()->updateOrCreate([
                'user_id' => $demoUser->id,
                'product_id' => $productMap[$cartItem['name']]->id,
            ], [
                'quantity' => $cartItem['quantity'],
            ]);
        }

        $allUsers = collect([$demoUser])->merge($showcaseUsers)->values();

        $this->seedOrder(
            $demoUser,
            'DIVA-SEED-000001',
            'paid',
            'paid',
            'demo_card',
            [
                ['name' => 'Demo Ring Rose Glow', 'quantity' => 1],
                ['name' => 'Necklace Demo Soft Rose', 'quantity' => 1],
            ],
            $productMap,
            3
        );

        $this->seedOrder(
            $allUsers[1],
            'DIVA-SEED-000002',
            'pending',
            'pending',
            'demo_card',
            [
                ['name' => 'Earrings Demo Blush Drop', 'quantity' => 1],
                ['name' => 'Demo Pendant Love Script', 'quantity' => 1],
            ],
            $productMap,
            2
        );

        $this->seedOrder(
            $allUsers[2],
            'DIVA-SEED-000003',
            'paid',
            'paid',
            'cash_on_delivery',
            [
                ['name' => 'Demo Bracelet Silk Chain', 'quantity' => 1],
                ['name' => 'Earrings Demo Crystal Hoop', 'quantity' => 2],
            ],
            $productMap,
            9
        );

        $this->seedOrder(
            $allUsers[3],
            'DIVA-SEED-000004',
            'failed',
            'failed',
            'demo_card',
            [
                ['name' => 'Necklace Demo Evening Pearl', 'quantity' => 1],
            ],
            $productMap,
            15
        );

        $this->seedOrder(
            $allUsers[4],
            'DIVA-SEED-000005',
            'paid',
            'paid',
            'demo_card',
            [
                ['name' => 'Demo Pendant Moon Charm', 'quantity' => 1],
                ['name' => 'Demo Ring Pearl Line', 'quantity' => 1],
                ['name' => 'Demo Bracelet Tender Pearl', 'quantity' => 1],
            ],
            $productMap,
            21
        );
    }

    private function seedOrder(
        User $user,
        string $reference,
        string $status,
        string $paymentStatus,
        string $paymentMethod,
        array $items,
        $productMap,
        int $daysAgo,
    ): void {
        $paidAt = $paymentStatus === 'paid' ? now()->subDays($daysAgo) : null;

        $order = Order::query()->updateOrCreate([
            'payment_reference' => $reference,
        ], [
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'payment_method' => $paymentMethod,
            'payment_provider' => $paymentMethod,
            'payment_reference' => $reference,
            'payment_status' => $paymentStatus,
            'paid_at' => $paidAt,
            'total' => 0,
            'status' => $status,
        ]);

        $order->items()->delete();

        foreach ($items as $item) {
            $product = $productMap[$item['name']];

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }

        $total = (float) $order->items()->get()->sum(
            fn (OrderItem $item): float => (float) $item->price * $item->quantity
        );

        $order->forceFill([
            'total' => $total,
            'created_at' => now()->subDays($daysAgo),
            'updated_at' => now()->subDays(max($daysAgo - 1, 0)),
        ])->saveQuietly();

        PaymentTransaction::query()->updateOrCreate([
            'reference' => $order->payment_reference,
        ], [
            'order_id' => $order->id,
            'provider' => $paymentMethod,
            'payment_method' => $paymentMethod,
            'reference' => $order->payment_reference,
            'provider_reference' => 'DEMO-' . str_replace('DIVA-', '', $order->payment_reference),
            'amount' => $total,
            'currency' => 'UAH',
            'status' => $paymentStatus,
            'provider_payload' => [
                'provider_status' => $paymentStatus,
                'source' => 'database_seeder',
            ],
            'paid_at' => $paymentStatus === 'paid' ? now()->subDays($daysAgo) : null,
            'failed_at' => $paymentStatus === 'failed' ? now()->subDays($daysAgo) : null,
        ]);
    }
}
