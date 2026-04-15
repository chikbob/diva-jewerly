<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Policies\Concerns\ChecksMoonShineSuperUser;
use MoonShine\Models\MoonshineUser;

class OrderItemPolicy
{
    use ChecksMoonShineSuperUser;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canManage($user);
    }

    public function view(MoonshineUser $user, OrderItem $orderItem): bool
    {
        return $this->canManage($user);
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
