<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class UserPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'customers', 'viewAny');
    }

    public function view(MoonshineUser $user, User $model): bool
    {
        return $this->canAccessDomain($user, 'customers', 'view');
    }

    public function create(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'customers', 'create');
    }

    public function update(MoonshineUser $user, User $model): bool
    {
        return $this->canAccessDomain($user, 'customers', 'update');
    }

    public function delete(MoonshineUser $user, User $model): bool
    {
        return $this->canAccessDomain($user, 'customers', 'delete');
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'customers', 'massDelete');
    }
}
