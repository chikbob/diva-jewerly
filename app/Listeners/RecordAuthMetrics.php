<?php

namespace App\Listeners;

use App\Support\MetricStore;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class RecordAuthMetrics
{
    public function __construct(private readonly MetricStore $metricStore)
    {
    }

    public function handle(Login|Logout|Registered|Verified|PasswordReset|Lockout|Failed $event): void
    {
        [$eventName, $guard] = match (true) {
            $event instanceof Login => ['login_succeeded', $event->guard],
            $event instanceof Logout => ['logout_completed', $event->guard],
            $event instanceof Registered => ['registration_completed', 'web'],
            $event instanceof Verified => ['email_verified', 'web'],
            $event instanceof PasswordReset => ['password_reset_completed', 'web'],
            $event instanceof Lockout => ['login_locked_out', 'web'],
            $event instanceof Failed => ['login_failed', $event->guard],
        };

        $this->metricStore->incrementCounter(
            'auth_events_total',
            'Authentication lifecycle events by event name and guard.',
            [
                'event' => $eventName,
                'guard' => $guard,
            ]
        );
    }
}
