<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /** Admin / staff panel sign-in screen (email + password). */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * Panel sign-in: authenticate an active account by email + password. The
     * consumer app uses OTP instead (see requestOtp / OtpController), so this
     * door is effectively admin/staff only — self-registered consumers have no
     * password to present.
     */
    public function authenticate(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $ok = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $request->boolean('remember'));

        if (! $ok) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
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

    /** Consumer app sign-in screen (mobile number + OTP). */
    public function showApp(): View
    {
        return view('auth.app-login');
    }

    /**
     * Step 1 of the consumer app sign-in: check the number belongs to an active
     * account and open an OTP challenge. Nothing is authenticated here —
     * OtpController completes the sign-in once the code checks out.
     */
    public function requestOtp(LoginRequest $request, OtpService $otp): RedirectResponse
    {
        $phone = $request->string('phone')->toString();

        $exists = User::query()
            ->where('phone', $phone)
            ->where('status', 'active')
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'phone' => __('No active account is registered with this mobile number.'),
            ]);
        }

        $otp->start($phone, OtpService::INTENT_LOGIN);
        $request->session()->put('auth.remember', $request->boolean('remember'));

        return redirect()->route('otp.show');
    }

    public function logout(OtpService $otp): RedirectResponse
    {
        $user = Auth::user();
        // Consumers came in through the app door, so send them back to it.
        $returnTo = $user !== null && $user->isConsumer() ? 'app.login' : 'login';

        if ($user !== null) {
            app(ActivityLogger::class)->log('logout', $user, "{$user->name} logged out", logName: 'auth');
        }

        $otp->clear();
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route($returnTo);
    }
}
