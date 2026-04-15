<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_super_moonshine_users_cannot_access_admin_panel(): void
    {
        Log::spy();

        $role = MoonshineUserRole::query()->create([
            'name' => 'Support',
        ]);

        $user = MoonshineUser::query()->create([
            'moonshine_user_role_id' => $role->id,
            'email' => 'support@example.com',
            'password' => bcrypt('password'),
            'name' => 'Support Agent',
        ]);

        $response = $this->actingAs($user, 'moonshine')->get('/admin');

        $response->assertForbidden();
        Log::shouldHaveReceived('warning')->withArgs(
            fn (string $message, array $context): bool => $message === 'admin.access.denied'
                && $context['moonshine_user_id'] === $user->getAuthIdentifier()
                && $context['admin_path'] === 'admin'
        )->once();
    }

    public function test_admin_policies_keep_orders_and_carts_read_only(): void
    {
        $admin = MoonshineUser::factory()->create([
            'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID,
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Order::class));
        $this->assertFalse(Gate::forUser($admin)->allows('create', Order::class));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', new Order()));
        $this->assertFalse(Gate::forUser($admin)->allows('update', new CartItem()));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', new OrderItem()));
    }

    public function test_admin_policies_allow_catalog_and_user_management_for_super_users(): void
    {
        $admin = MoonshineUser::factory()->create([
            'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID,
        ]);

        $this->assertTrue(Gate::forUser($admin)->allows('create', Product::class));
        $this->assertTrue(Gate::forUser($admin)->allows('update', new Category()));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', new User()));
    }

    public function test_app_user_passwords_are_hashed_when_assigned_directly(): void
    {
        $user = User::factory()->create();
        $user->password = 'changed-from-admin-panel';
        $user->save();

        $this->assertNotSame('changed-from-admin-panel', $user->fresh()->password);
        $this->assertTrue(Hash::check('changed-from-admin-panel', $user->fresh()->password));
    }
}
