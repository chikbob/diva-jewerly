<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CartCounter
{
    public static function countFor(User $user): int
    {
        return Cache::remember(
            static::key($user->id),
            now()->addMinutes(10),
            static fn (): int => (int) $user->cartItems()->sum('quantity')
        );
    }

    public static function forgetForUserId(?int $userId): void
    {
        if ($userId === null) {
            return;
        }

        Cache::forget(static::key($userId));
    }

    private static function key(int $userId): string
    {
        return "cart_count:user:{$userId}";
    }
}
