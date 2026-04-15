<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMetricsToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) config('operations.metrics.token', '');

        if ($token === '') {
            return $next($request);
        }

        $providedToken = $request->bearerToken() ?? (string) $request->query('token', '');

        abort_if(! hash_equals($token, $providedToken), Response::HTTP_FORBIDDEN);

        return $next($request);
    }
}
