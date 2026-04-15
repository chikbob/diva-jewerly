<?php

namespace App\Listeners;

use App\Support\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class LogAuthActivity
{
    public function handle(Login|Logout|Registered|Verified|PasswordReset|Lockout|Failed $event): void
    {
        match (true) {
            $event instanceof Login => AuditLogger::info('auth.login.succeeded', [
                'auth_user_id' => $event->user->getAuthIdentifier(),
                'guard' => $event->guard,
                'remember' => $event->remember,
            ]),
            $event instanceof Logout => AuditLogger::info('auth.logout.completed', [
                'auth_user_id' => $event->user?->getAuthIdentifier(),
                'guard' => $event->guard,
            ]),
            $event instanceof Registered => AuditLogger::info('auth.registration.completed', [
                'auth_user_id' => $event->user->getAuthIdentifier(),
            ]),
            $event instanceof Verified => AuditLogger::info('auth.email.verified', [
                'auth_user_id' => $event->user->getAuthIdentifier(),
            ]),
            $event instanceof PasswordReset => AuditLogger::info('auth.password.reset_completed', [
                'auth_user_id' => $event->user->getAuthIdentifier(),
            ]),
            $event instanceof Lockout => AuditLogger::warning('auth.login.locked_out', [
                'email_hash' => AuditLogger::hashIdentifier((string) $event->request->input('email')),
                'throttle_key' => method_exists($event->request, 'throttleKey')
                    ? $event->request->throttleKey()
                    : null,
            ]),
            $event instanceof Failed => AuditLogger::warning('auth.login.failed', [
                'auth_user_id' => $event->user?->getAuthIdentifier(),
                'guard' => $event->guard,
                'email_hash' => AuditLogger::hashIdentifier((string) ($event->credentials['email'] ?? '')),
            ]),
        };
    }
}
