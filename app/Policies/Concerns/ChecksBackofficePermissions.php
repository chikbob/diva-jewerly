<?php

namespace App\Policies\Concerns;

use App\Support\BackofficeAccess;
use MoonShine\Models\MoonshineUser;

trait ChecksBackofficePermissions
{
    protected function canAccessDomain(MoonshineUser $user, string $domain, string $ability, bool $readOnly = false): bool
    {
        return app(BackofficeAccess::class)->allowsDomainAbility($user, $domain, $ability, $readOnly);
    }
}
