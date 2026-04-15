<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class CartItemPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'operations', 'viewAny', true);
    }

    public function view(MoonshineUser $user, CartItem $cartItem): bool
    {
        return $this->canAccessDomain($user, 'operations', 'view', true);
    }

    public function create(MoonshineUser $user): bool
    {
        return false;
    }

    public function update(MoonshineUser $user, CartItem $cartItem): bool
    {
        return false;
    }

    public function delete(MoonshineUser $user, CartItem $cartItem): bool
    {
        return false;
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return false;
    }
}
