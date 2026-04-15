<?php

namespace App\Policies;

use App\Models\Product;
use App\Policies\Concerns\ChecksMoonShineSuperUser;
use MoonShine\Models\MoonshineUser;

class ProductPolicy
{
    use ChecksMoonShineSuperUser;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function view(MoonshineUser $user, Product $product): bool
    {
        return $this->canManage($user);
    }

    public function create(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function update(MoonshineUser $user, Product $product): bool
    {
        return $this->canManage($user);
    }

    public function delete(MoonshineUser $user, Product $product): bool
    {
        return $this->canManage($user);
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }
}
