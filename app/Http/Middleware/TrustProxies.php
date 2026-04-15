<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct(Application $app, Router $router)
    {
        $this->proxies = $this->resolveTrustedProxies();
    }

    /**
     * @return array<int, string>|string|null
     */
    private function resolveTrustedProxies(): array|string|null
    {
        $proxies = trim((string) env('TRUSTED_PROXIES', ''));

        if ($proxies === '') {
            return null;
        }

        if ($proxies === '*') {
            return '*';
        }

        return array_values(array_map(
            static fn (string $proxy): string => trim($proxy),
            array_filter(explode(',', $proxies))
        ));
    }
}
