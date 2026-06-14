<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;
use Tests\TestCase;

class OrderReceiptAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_and_download_own_receipt(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'full_name' => 'Test User',
            'email' => 'buyer@example.com',
            'payment_reference' => 'DIVA-RECEIPT-001',
            'payment_status' => 'paid',
            'status' => 'paid',
            'total' => 4200,
        ]);
        $product = Product::factory()->create([
            'name' => 'Golden Ring',
            'price' => 2100,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 2100,
        ]);

        $this->actingAs($user)
            ->get(route('orders.receipt.show', ['order' => $order]))
            ->assertOk()
            ->assertSee('Чек замовлення #'.$order->id)
            ->assertSee('Golden Ring');

        $this->actingAs($user)
            ->get(route('orders.receipt.download', ['order' => $order]))
            ->assertOk()
            ->assertSee('downloadReceiptPdf()');
    }

    public function test_user_cannot_access_another_users_receipt(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->get(route('orders.receipt.show', ['order' => $order]))
            ->assertNotFound();
    }

    public function test_admin_can_view_orders_report_and_export_csv(): void
    {
        $admin = $this->createMoonshineUser('Admin');
        $customer = User::factory()->create();

        $paidOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'payment_status' => 'paid',
            'status' => 'paid',
            'total' => 5000,
        ]);

        $pendingOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'payment_status' => 'pending',
            'status' => 'pending',
            'total' => 1500,
        ]);

        $product = Product::factory()->create([
            'name' => 'Pearl Earrings',
            'price' => 2500,
        ]);

        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 2500,
        ]);

        OrderItem::query()->create([
            'order_id' => $pendingOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1500,
        ]);

        $this->actingAs($admin, 'moonshine')
            ->get(route('admin.reports.orders', ['payment_status' => 'paid']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Orders')
                ->where('filters.payment_status', 'paid')
                ->where('summary.orders_count', 1)
                ->where('summary.revenue_total', 5000.0)
                ->where('topProducts.0.product_name', 'Pearl Earrings')
            );

        $this->actingAs($admin, 'moonshine')
            ->get(route('admin.reports.orders.export', ['payment_status' => 'paid']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function createMoonshineUser(string $roleName): MoonshineUser
    {
        $role = MoonshineUserRole::query()->firstOrCreate([
            'name' => $roleName,
        ]);

        return MoonshineUser::query()->create([
            'moonshine_user_role_id' => $role->id,
            'email' => strtolower(str_replace(' ', '.', $roleName)).'.'.uniqid('', true).'@example.com',
            'password' => bcrypt('password'),
            'name' => $roleName.' Agent',
        ]);
    }
}
