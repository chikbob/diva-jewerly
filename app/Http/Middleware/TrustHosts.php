<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return array_values(array_filter([
            $this->allSubdomainsOfApplicationUrl(),
            ...array_map(
                static fn (string $host): string => trim($host),
                array_filter(explode(',', (string) env('TRUSTED_HOSTS', '')))
            ),
        ]));
    }
}
