<?php

namespace App\Providers;

use App\Listeners\LogAuthActivity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            LogAuthActivity::class,
        ],
        Login::class => [
            LogAuthActivity::class,
        ],
        Logout::class => [
            LogAuthActivity::class,
        ],
        Verified::class => [
            LogAuthActivity::class,
        ],
        PasswordReset::class => [
            LogAuthActivity::class,
        ],
        Lockout::class => [
            LogAuthActivity::class,
        ],
        Failed::class => [
            LogAuthActivity::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
