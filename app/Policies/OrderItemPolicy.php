<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class OrderItemPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'operations', 'viewAny', true);
    }

    public function view(MoonshineUser $user, OrderItem $orderItem): bool
    {
        return $this->canAccessDomain($user, 'operations', 'view', true);
    }

    public function create(MoonshineUser $user): bool
    {
        return false;
    }

    public function update(MoonshineUser $user, OrderItem $orderItem): bool
    {
        return false;
    }

    public function delete(MoonshineUser $user, OrderItem $orderItem): bool
    {
        return false;
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return false;
    }
}
