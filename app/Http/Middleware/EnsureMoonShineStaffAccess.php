<?php

namespace App\Http\Middleware;

use App\Support\AuditLogger;
use App\Support\BackofficeAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureMoonShineStaffAccess
{
    public function __construct(
        private readonly BackofficeAccess $backofficeAccess,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();

        if ($user !== null && ! $this->backofficeAccess->canAccessPanel($user)) {
            AuditLogger::warning('admin.access.denied', [
                'admin_path' => $request->path(),
                'admin_method' => $request->method(),
                'moonshine_user_id' => $user->getAuthIdentifier(),
            ]);

            abort(Response::HTTP_FORBIDDEN);
        }

        if ($user !== null && ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            AuditLogger::info('admin.request.mutating', [
                'admin_path' => $request->path(),
                'admin_method' => $request->method(),
                'moonshine_user_id' => $user->getAuthIdentifier(),
            ]);
        }

        return $next($request);
    }
}
