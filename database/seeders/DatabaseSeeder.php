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
            'name' => 'Демо Олена Ковальчук',
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
            'name' => 'Демо Ірина Левченко',
            'password' => Hash::make('admin12345'),
        ]);

        $showcaseUsers = collect([
            [
                'name' => 'Демо Марина Гаврилюк',
                'email' => 'marina.demo@diva.local',
            ],
            [
                'name' => 'Демо Софія Мельник',
                'email' => 'sofia.demo@diva.local',
            ],
            [
                'name' => 'Демо Наталія Романюк',
                'email' => 'natalia.demo@diva.local',
            ],
            [
                'name' => 'Демо Катерина Шевчук',
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
                'name' => 'Каблучки',
                'description' => 'Вишукані каблучки для щоденного образу та особливих подій.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Каблучка Демо Rose Glow', 'description' => 'Позолочена каблучка з рожевим кристалом для делікатного щоденного акценту.', 'price' => 2680.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 4],
                    ['name' => 'Каблучка Демо Emerald Promise', 'description' => 'Каблучка з зеленим каменем і доріжкою фіанітів у сучасній оправі.', 'price' => 7191.91, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 10],
                    ['name' => 'Каблучка Демо Golden Lace', 'description' => 'Тонка ажурна модель для комбінування з іншими прикрасами.', 'price' => 3190.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 38],
                    ['name' => 'Каблучка Демо Pearl Line', 'description' => 'Лаконічна каблучка з перловим акцентом для офісного стилю.', 'price' => 4120.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 22],
                ],
            ],
            [
                'name' => 'Сережки',
                'description' => 'Класичні та сучасні сережки з акцентом на стиль і легкість.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Сережки Демо Blush Drop', 'description' => 'Подовжені сережки з делікатним рожевим блиском для вечірнього образу.', 'price' => 3580.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 7],
                    ['name' => 'Сережки Демо Crystal Hoop', 'description' => 'Акуратні сережки-кільця з інкрустацією для щоденного носіння.', 'price' => 2890.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 14],
                    ['name' => 'Сережки Демо Ivory Spark', 'description' => 'Ніжна модель із світлим каменем, що пасує до базового гардероба.', 'price' => 4310.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 45],
                    ['name' => 'Сережки Демо Velvet Shine', 'description' => 'Мінімалістична пара з м’яким золотим блиском та делікатною геометрією.', 'price' => 3985.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 3],
                ],
            ],
            [
                'name' => 'Підвіски',
                'description' => 'Акуратні підвіски, що підкреслюють індивідуальність.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Підвіска Демо Moon Charm', 'description' => 'Романтична підвіска з гладким металом і акцентом на чисту форму.', 'price' => 3907.51, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 12],
                    ['name' => 'Підвіска Демо Love Script', 'description' => 'Легка підвіска з виразним контуром для подарункового настрою.', 'price' => 2470.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 29],
                    ['name' => 'Підвіска Демо Aurora Star', 'description' => 'Яскравий акцент із каменем холодного сяйва в тонкому ланцюжку.', 'price' => 4510.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 61],
                    ['name' => 'Підвіска Демо Signature Heart', 'description' => 'Комерційна bestseller-модель з м’якою формою серця.', 'price' => 3325.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 5],
                ],
            ],
            [
                'name' => 'Браслети',
                'description' => 'Браслети для повсякденного носіння та святкових образів.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Браслет Демо Silk Chain', 'description' => 'Легкий ланцюговий браслет, який добре поєднується з годинником.', 'price' => 4850.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 8],
                    ['name' => 'Браслет Демо Tender Pearl', 'description' => 'Модель з перловою вставкою для стриманого жіночного стилю.', 'price' => 5290.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 18],
                    ['name' => 'Браслет Демо Milano Shine', 'description' => 'Фактурне плетіння з виразним блиском для подарункової колекції.', 'price' => 6120.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 41],
                    ['name' => 'Браслет Демо Daily Grace', 'description' => 'Комфортний базовий браслет на кожен день з м’якою посадкою.', 'price' => 2760.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 2],
                ],
            ],
            [
                'name' => 'Кольє',
                'description' => 'Елегантні кольє для завершення ювелірного комплекту.',
                'image_url' => 'https://g0.sunlight.net/media/products/ef9aacf9-7003-11ef-b516-005056bccafe.jpg',
                'products' => [
                    ['name' => 'Кольє Демо Soft Rosé', 'description' => 'Рожевий тон і м’яка лінія підкреслюють комерційний premium-настрій колекції.', 'price' => 6890.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 9],
                    ['name' => 'Кольє Демо Evening Pearl', 'description' => 'Витончене кольє для суконь з відкритою лінією шиї.', 'price' => 7420.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 27],
                    ['name' => 'Кольє Демо Riviera Light', 'description' => 'Святкова модель з виразним блиском та рівномірною посадкою.', 'price' => 9150.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 55],
                    ['name' => 'Кольє Демо City Minimal', 'description' => 'Мінімалістичне кольє для базового гардероба і сучасного стилю.', 'price' => 4380.00, 'image_path' => 'https://g6.sunlight.net/media/products/61d5da03c8fe908a0b95f5422284bf6cbf7d36f6.jpg', 'days_ago' => 1],
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
            'Каблучка Демо Rose Glow',
            'Сережки Демо Velvet Shine',
            'Підвіска Демо Signature Heart',
            'Кольє Демо Soft Rosé',
        ];

        foreach ($favoriteProducts as $productName) {
            Favorite::query()->updateOrCreate([
                'user_id' => $demoUser->id,
                'product_id' => $productMap[$productName]->id,
            ]);
        }

        foreach ([
            ['name' => 'Каблучка Демо Emerald Promise', 'quantity' => 1],
            ['name' => 'Браслет Демо Daily Grace', 'quantity' => 2],
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
                ['name' => 'Каблучка Демо Rose Glow', 'quantity' => 1],
                ['name' => 'Кольє Демо Soft Rosé', 'quantity' => 1],
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
                ['name' => 'Сережки Демо Blush Drop', 'quantity' => 1],
                ['name' => 'Підвіска Демо Love Script', 'quantity' => 1],
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
                ['name' => 'Браслет Демо Silk Chain', 'quantity' => 1],
                ['name' => 'Сережки Демо Crystal Hoop', 'quantity' => 2],
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
                ['name' => 'Кольє Демо Evening Pearl', 'quantity' => 1],
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
                ['name' => 'Підвіска Демо Moon Charm', 'quantity' => 1],
                ['name' => 'Каблучка Демо Pearl Line', 'quantity' => 1],
                ['name' => 'Браслет Демо Tender Pearl', 'quantity' => 1],
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
