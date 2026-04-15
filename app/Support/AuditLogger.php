<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function info(string $event, array $context = []): void
    {
        Log::info($event, self::buildContext($event, $context));
    }

    public static function warning(string $event, array $context = []): void
    {
        Log::warning($event, self::buildContext($event, $context));
    }

    public static function hashIdentifier(string $value): ?string
    {
        $normalized = strtolower(trim($value));

        return $normalized === '' ? null : hash('sha256', $normalized);
    }

    private static function buildContext(string $event, array $context = []): array
    {
        $request = app()->bound('request') ? request() : null;
        $webUser = Auth::guard('web')->user();
        $moonshineGuard = (string) config('moonshine.auth.guard', 'moonshine');
        $moonshineUser = Auth::guard($moonshineGuard)->user();

        return array_filter(array_merge([
            'event' => $event,
            'request_id' => $request?->attributes->get('request_id'),
            'route' => $request?->route()?->getName(),
            'method' => $request?->method(),
            'path' => $request?->path(),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id' => self::resolveId($webUser),
            'moonshine_user_id' => self::resolveId($moonshineUser),
        ], $context), static fn ($value) => $value !== null && $value !== '');
    }

    private static function resolveId(mixed $user): int|string|null
    {
        return $user instanceof Authenticatable ? $user->getAuthIdentifier() : null;
    }
}
