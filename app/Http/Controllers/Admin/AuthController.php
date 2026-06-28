<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BackofficeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly BackofficeAccess $backofficeAccess,
    ) {}

    public function create(Request $request): Response|RedirectResponse
    {
        $guard = config('moonshine.auth.guard', 'moonshine');
        $user = Auth::guard($guard)->user();

        if ($user !== null && $this->backofficeAccess->canAccessPanel($user)) {
            return redirect()->route('admin.dashboard');
        }

        if ($user !== null) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return Inertia::render('Admin/Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = config('moonshine.auth.guard', 'moonshine');

        if (! Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard($guard)->user();

        if (! $this->backofficeAccess->canAccessPanel($user)) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account does not have access to the backoffice.',
            ])->onlyInput('email');
        }

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard(config('moonshine.auth.guard', 'moonshine'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
