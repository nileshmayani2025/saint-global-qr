<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, ActivityLogger $logger): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::attempt([...$credentials, 'status' => 'active'], $remember)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records, or the account is inactive.'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        $logger->log('login', $user, "{$user->name} logged in", logName: 'auth');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            app(ActivityLogger::class)->log('logout', $user, "{$user->name} logged out", logName: 'auth');
        }

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
