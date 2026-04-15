<?php

namespace App\Policies;

use App\Models\Category;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class CategoryPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'viewAny');
    }

    public function view(MoonshineUser $user, Category $category): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'view');
    }

    public function create(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'create');
    }

    public function update(MoonshineUser $user, Category $category): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'update');
    }

    public function delete(MoonshineUser $user, Category $category): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'delete');
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'catalog', 'massDelete');
    }
}
