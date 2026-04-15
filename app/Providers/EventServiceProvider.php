<?php

namespace App\Providers;

use App\Listeners\LogAuthActivity;
use App\Listeners\RecordAuthMetrics;
use App\Listeners\RecordQueueMetrics;
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
use Illuminate\Support\Facades\Queue;

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
            RecordAuthMetrics::class,
        ],
        Login::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
        Logout::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
        Verified::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
        PasswordReset::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
        Lockout::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
        Failed::class => [
            LogAuthActivity::class,
            RecordAuthMetrics::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        $queueMetrics = $this->app->make(RecordQueueMetrics::class);

        Queue::before([$queueMetrics, 'whenProcessing']);
        Queue::after([$queueMetrics, 'whenProcessed']);
        Queue::failing([$queueMetrics, 'whenFailed']);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
