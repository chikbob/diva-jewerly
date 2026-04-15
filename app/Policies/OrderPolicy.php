<?php

namespace App\Policies;

use App\Models\Order;
use App\Policies\Concerns\ChecksMoonShineSuperUser;
use MoonShine\Models\MoonshineUser;

class OrderPolicy
{
    use ChecksMoonShineSuperUser;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function view(MoonshineUser $user, Order $order): bool
    {
        return $this->canManage($user);
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
