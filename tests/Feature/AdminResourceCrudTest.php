<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;
use Tests\TestCase;

class AdminResourceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_product_from_custom_admin_panel(): void
    {
        $admin = $this->createMoonshineUser('Admin');
        $category = Category::factory()->create([
            'name' => 'Каблучки',
        ]);

        $createResponse = $this->actingAs($admin, 'moonshine')->post(route('admin.resources.store', [
            'resource' => 'products',
        ]), [
            'name' => 'Diva Signature Ring',
            'category_id' => $category->id,
            'price' => '1999.99',
            'image_path' => 'https://example.com/ring.jpg',
            'description' => 'Флагманська прикраса для вітрини.',
        ]);

        $createResponse->assertRedirect(route('admin.resources.index', ['resource' => 'products']));
        $this->assertDatabaseHas('products', [
            'name' => 'Diva Signature Ring',
            'category_id' => $category->id,
        ]);

        $productId = \App\Models\Product::query()->where('name', 'Diva Signature Ring')->value('id');

        $updateResponse = $this->actingAs($admin, 'moonshine')->put(route('admin.resources.update', [
            'resource' => 'products',
            'record' => $productId,
        ]), [
            'name' => 'Diva Signature Ring Updated',
            'category_id' => $category->id,
            'price' => '2499.50',
            'image_path' => 'https://example.com/ring-updated.jpg',
            'description' => 'Оновлений опис прикраси.',
        ]);

        $updateResponse->assertRedirect(route('admin.resources.index', ['resource' => 'products']));
        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'name' => 'Diva Signature Ring Updated',
            'price' => '2499.50',
        ]);
    }

    public function test_admin_orders_resource_supports_filters_and_sorting(): void
    {
        $admin = $this->createMoonshineUser('Admin');
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'demo_card',
            'total' => 1500,
            'payment_reference' => 'DIVA-ADMIN-001',
        ]);

        $target = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'total' => 3200,
            'payment_reference' => 'DIVA-ADMIN-002',
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'paid',
            'payment_status' => 'failed',
            'payment_method' => 'cash_on_delivery',
            'total' => 900,
            'payment_reference' => 'DIVA-ADMIN-003',
        ]);

        $this->actingAs($admin, 'moonshine')
            ->get(route('admin.resources.index', [
                'resource' => 'orders',
                'status' => 'paid',
                'payment_status' => 'paid',
                'sort' => 'total',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Resources/Index')
                ->where('resource.key', 'orders')
                ->where('filters.status', 'paid')
                ->where('filters.payment_status', 'paid')
                ->where('filters.sort', 'total')
                ->where('filters.direction', 'asc')
                ->has('records.data', 1)
                ->where('records.data.0.title', 'DIVA-ADMIN-002')
                ->where('records.data.0.id', $target->id)
            );
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
