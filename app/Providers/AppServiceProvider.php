<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Support\Access\AccessControl;
use App\Support\Phone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One audit logger per request lifecycle.
        $this->app->singleton(ActivityLogger::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiters();

        // Super-admin bypasses every permission check.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole(AccessControl::ROLE_SUPER_ADMIN) ? true : null;
        });

        // Spatie's Role model lives outside App\Models, so map its policy explicitly.
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);
    }

    private function configureRateLimiters(): void
    {
        // General authenticated API traffic.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        // Public product verification — tighter per-IP ceiling to deter scanners.
        RateLimiter::for('verify', fn (Request $request) => [
            Limit::perMinute(30)->by($request->ip()),
            Limit::perDay(500)->by($request->ip()),
        ]);

        // OTP requests — strict, keyed on the number being verified so one
        // caller can't burn through codes for many accounts from one IP.
        RateLimiter::for('otp', fn (Request $request) => Limit::perMinute(5)
            ->by(Phone::normalize($request->input('phone')) ?: $request->ip()));

        // Admin panel sign-in — keyed on the email being tried plus IP, so a
        // password-guessing attempt is throttled without locking out others.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(6)
            ->by(strtolower((string) $request->input('email')).'|'.$request->ip()));
    }
}
