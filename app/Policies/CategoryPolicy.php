<?php

namespace App\Policies;

use App\Models\Category;
use App\Policies\Concerns\ChecksMoonShineSuperUser;
use MoonShine\Models\MoonshineUser;

class CategoryPolicy
{
    use ChecksMoonShineSuperUser;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function view(MoonshineUser $user, Category $category): bool
    {
        return $this->canManage($user);
    }

    public function create(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function update(MoonshineUser $user, Category $category): bool
    {
        return $this->canManage($user);
    }

    public function delete(MoonshineUser $user, Category $category): bool
    {
        return $this->canManage($user);
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }
}
