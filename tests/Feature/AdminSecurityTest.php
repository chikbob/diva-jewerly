<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Support\BackofficeAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_backoffice_roles_cannot_access_admin_panel(): void
    {
        Log::spy();

        $role = MoonshineUserRole::query()->create([
            'name' => 'Intern',
        ]);

        $user = MoonshineUser::query()->create([
            'moonshine_user_role_id' => $role->id,
            'email' => 'intern@example.com',
            'password' => bcrypt('password'),
            'name' => 'Intern',
        ]);

        $response = $this->actingAs($user, 'moonshine')->get('/admin');

        $response->assertForbidden();
        Log::shouldHaveReceived('warning')->withArgs(
            fn (string $message, array $context): bool => $message === 'admin.access.denied'
                && $context['moonshine_user_id'] === $user->getAuthIdentifier()
                && $context['admin_path'] === 'admin'
        )->once();
    }

    public function test_support_staff_can_access_admin_panel_but_only_has_read_permissions(): void
    {
        $support = $this->createMoonshineUser('Support');

        $response = $this->actingAs($support, 'moonshine')->get('/admin');

        $response->assertOk();

        $this->assertTrue(Gate::forUser($support)->allows('viewAny', Order::class));
        $this->assertTrue(Gate::forUser($support)->allows('viewAny', PaymentTransaction::class));
        $this->assertTrue(Gate::forUser($support)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($support)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($support)->allows('update', new User));
        $this->assertFalse(Gate::forUser($support)->allows('delete', new Order));
    }

    public function test_operations_manager_can_review_orders_and_customers_without_mutating_them(): void
    {
        $operations = $this->createMoonshineUser('Operations Manager');

        $this->assertTrue(Gate::forUser($operations)->allows('viewAny', Order::class));
        $this->assertTrue(Gate::forUser($operations)->allows('viewAny', CartItem::class));
        $this->assertTrue(Gate::forUser($operations)->allows('viewAny', User::class));
        $this->assertFalse(Gate::forUser($operations)->allows('create', Category::class));
        $this->assertFalse(Gate::forUser($operations)->allows('update', new CartItem));
        $this->assertFalse(Gate::forUser($operations)->allows('delete', new OrderItem));
    }

    public function test_catalog_manager_can_manage_catalog_but_not_customers_or_operations(): void
    {
        $catalogManager = $this->createMoonshineUser('Catalog Manager');

        $this->assertTrue(Gate::forUser($catalogManager)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($catalogManager)->allows('update', new Category));
        $this->assertFalse(Gate::forUser($catalogManager)->allows('viewAny', Order::class));
        $this->assertFalse(Gate::forUser($catalogManager)->allows('create', User::class));
    }

    public function test_admin_staff_can_manage_catalog_users_and_staff_resources_while_orders_stay_read_only(): void
    {
        $admin = $this->createMoonshineUser('Admin');

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Order::class));
        $this->assertFalse(Gate::forUser($admin)->allows('create', Order::class));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', new Order));
        $this->assertFalse(Gate::forUser($admin)->allows('update', new CartItem));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', new OrderItem));

        $this->assertTrue(Gate::forUser($admin)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', new Category));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', new User));

        $access = app(BackofficeAccess::class);

        $this->assertTrue($access->canAccessResource($admin, new MoonShineUserResource, 'viewAny'));
        $this->assertTrue($access->canAccessResource($admin, new MoonShineUserRoleResource, 'viewAny'));
    }

    public function test_non_admin_staff_cannot_manage_staff_resources(): void
    {
        $support = $this->createMoonshineUser('Support');

        $access = app(BackofficeAccess::class);

        $this->assertFalse($access->canAccessResource($support, new MoonShineUserResource, 'viewAny'));
        $this->assertFalse($access->canAccessResource($support, new MoonShineUserRoleResource, 'viewAny'));
    }

    public function test_app_user_passwords_are_hashed_when_assigned_directly(): void
    {
        $user = User::factory()->create();
        $user->password = 'changed-from-admin-panel';
        $user->save();

        $this->assertNotSame('changed-from-admin-panel', $user->fresh()->password);
        $this->assertTrue(Hash::check('changed-from-admin-panel', $user->fresh()->password));
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
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
