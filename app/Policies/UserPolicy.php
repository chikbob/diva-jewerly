<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksMoonShineSuperUser;
use MoonShine\Models\MoonshineUser;

class UserPolicy
{
    use ChecksMoonShineSuperUser;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function view(MoonshineUser $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function create(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function update(MoonshineUser $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function delete(MoonshineUser $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }
}
