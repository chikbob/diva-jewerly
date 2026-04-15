<?php

namespace App\Policies;

use App\Models\Order;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class OrderPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'operations', 'viewAny', true);
    }

    public function view(MoonshineUser $user, Order $order): bool
    {
        return $this->canAccessDomain($user, 'operations', 'view', true);
    }

    public function create(MoonshineUser $user): bool
    {
        return false;
    }

    public function update(MoonshineUser $user, Order $order): bool
    {
        return false;
    }

    public function delete(MoonshineUser $user, Order $order): bool
    {
        return false;
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return false;
    }
}
