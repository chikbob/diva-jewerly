<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        putenv('APP_ENV=testing');
        putenv('LOG_CHANNEL=stack');
        putenv('CACHE_DRIVER=array');
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');
        putenv('MAIL_MAILER=array');
        putenv('QUEUE_CONNECTION=sync');
        putenv('SESSION_DRIVER=array');

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['LOG_CHANNEL'] = 'stack';
        $_ENV['CACHE_DRIVER'] = 'array';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['MAIL_MAILER'] = 'array';
        $_ENV['QUEUE_CONNECTION'] = 'sync';
        $_ENV['SESSION_DRIVER'] = 'array';

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['LOG_CHANNEL'] = 'stack';
        $_SERVER['CACHE_DRIVER'] = 'array';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = ':memory:';
        $_SERVER['MAIL_MAILER'] = 'array';
        $_SERVER['QUEUE_CONNECTION'] = 'sync';
        $_SERVER['SESSION_DRIVER'] = 'array';
        
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
