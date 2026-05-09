<?php

namespace App\Policies;

use App\Models\PaymentTransaction;
use App\Policies\Concerns\ChecksBackofficePermissions;
use MoonShine\Models\MoonshineUser;

class PaymentTransactionPolicy
{
    use ChecksBackofficePermissions;

    public function viewAny(MoonshineUser $user): bool
    {
        return $this->canAccessDomain($user, 'operations', 'viewAny', true);
    }

    public function view(MoonshineUser $user, PaymentTransaction $paymentTransaction): bool
    {
        return $this->canAccessDomain($user, 'operations', 'view', true);
    }

    public function create(MoonshineUser $user): bool
    {
        return false;
    }

    public function update(MoonshineUser $user, PaymentTransaction $paymentTransaction): bool
    {
        return false;
    }

    public function delete(MoonshineUser $user, PaymentTransaction $paymentTransaction): bool
    {
        return false;
    }

    public function massDelete(MoonshineUser $user): bool
    {
        return false;
    }
}
