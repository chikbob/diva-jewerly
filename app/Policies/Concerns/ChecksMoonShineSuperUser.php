<?php

namespace App\Policies\Concerns;

use MoonShine\Models\MoonshineUser;

trait ChecksMoonShineSuperUser
{
    protected function canManage(MoonshineUser $user): bool
    {
        return $user->isSuperUser();
    }
}
