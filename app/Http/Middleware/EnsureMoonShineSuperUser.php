<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMoonShineSuperUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();

        if ($user !== null && ! $user->isSuperUser()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
