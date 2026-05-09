<?php

namespace App\Support;

use App\MoonShine\Resources\CartItemResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\OrderItemResource;
use App\MoonShine\Resources\OrderResource;
use App\MoonShine\Resources\PaymentTransactionResource;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\UserResource;
use Illuminate\Support\Str;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Models\MoonshineUser;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;

class BackofficeAccess
{
    public function canAccessPanel(mixed $user): bool
    {
        return $user instanceof MoonshineUser
            && $this->hasPermission($user, 'panel.access');
    }

    public function hasPermission(mixed $user, string $permission): bool
    {
        if (! $user instanceof MoonshineUser) {
            return false;
        }

        return in_array($permission, $this->permissionsFor($user), true);
    }

    /**
     * @return list<string>
     */
    public function permissionsFor(MoonshineUser $user): array
    {
        $role = $this->roleKeyFor($user);

        if ($role === null) {
            return [];
        }

        $permissions = config("backoffice.permissions.{$role}", []);

        return is_array($permissions) ? array_values(array_unique($permissions)) : [];
    }

    public function roleKeyFor(MoonshineUser $user): ?string
    {
        $user->loadMissing('moonshineUserRole');

        $normalized = $this->normalizeRoleName($user->moonshineUserRole?->name);

        if ($normalized === null) {
            return null;
        }

        return config("backoffice.role_aliases.{$normalized}", $normalized);
    }

    public function canAccessResource(mixed $user, mixed $resource, string $ability): bool
    {
        if (! $this->canAccessPanel($user)) {
            return false;
        }

        if (! $resource instanceof ResourceContract) {
            return true;
        }

        return match (true) {
            $resource instanceof MoonShineUserResource => $this->allowsDomainAbility($user, 'admins', $ability),
            $resource instanceof MoonShineUserRoleResource => $this->allowsDomainAbility($user, 'roles', $ability),
            $resource instanceof ProductResource, $resource instanceof CategoryResource => $this->allowsDomainAbility($user, 'catalog', $ability),
            $resource instanceof UserResource => $this->allowsDomainAbility($user, 'customers', $ability),
            $resource instanceof OrderResource, $resource instanceof OrderItemResource, $resource instanceof CartItemResource, $resource instanceof PaymentTransactionResource => $this->allowsDomainAbility($user, 'operations', $ability, true),
            default => true,
        };
    }

    public function canAccessAdminDomain(MoonshineUser $user, string $domain, string $ability): bool
    {
        if (! $this->canAccessPanel($user)) {
            return false;
        }

        if ($this->isReadAbility($ability)) {
            return $this->hasPermission($user, "{$domain}.view")
                || $this->hasPermission($user, "{$domain}.manage");
        }

        if ($domain === 'operations' && $this->roleKeyFor($user) === 'admin') {
            return true;
        }

        return $this->hasPermission($user, "{$domain}.manage");
    }

    public function allowsDomainAbility(MoonshineUser $user, string $domain, string $ability, bool $readOnly = false): bool
    {
        if ($this->isReadAbility($ability)) {
            return $this->hasPermission($user, "{$domain}.view")
                || $this->hasPermission($user, "{$domain}.manage");
        }

        if ($readOnly) {
            return false;
        }

        return $this->hasPermission($user, "{$domain}.manage");
    }

    private function normalizeRoleName(?string $roleName): ?string
    {
        if ($roleName === null) {
            return null;
        }

        $normalized = Str::of($roleName)
            ->trim()
            ->lower()
            ->replace(['/', '\\'], ' ')
            ->slug('_')
            ->value();

        return $normalized !== '' ? $normalized : null;
    }

    private function isReadAbility(string $ability): bool
    {
        return in_array($ability, [
            'index',
            'detail',
            'view',
            'viewAny',
        ], true);
    }
}
