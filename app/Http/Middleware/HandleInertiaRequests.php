<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\CartCounter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Throwable;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $appUser = $request->user() instanceof User ? $request->user() : null;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $appUser,
            ],
            'backoffice' => [
                'user' => fn () => $this->backofficeUser(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cartCount' => fn () => $appUser !== null
                ? CartCounter::countFor($appUser)
                : 0,
            'favoritesCount' => fn () => $appUser !== null
                ? $appUser->favorites()->count()
                : 0,
        ]);
    }

    private function backofficeUser(): ?array
    {
        $guard = (string) config('moonshine.auth.guard', 'moonshine');

        if (! is_array(config("auth.guards.{$guard}"))) {
            return null;
        }

        try {
            return Auth::guard($guard)->user()?->only([
                    'id',
                    'name',
                    'email',
            ]);
        } catch (Throwable) {
            return null;
        }
    }
}
