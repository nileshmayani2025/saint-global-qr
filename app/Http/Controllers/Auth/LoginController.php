<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Services\Auth\OtpService;
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

    /**
     * Step 1 of sign-in: check the number belongs to an active account and open
     * an OTP challenge. Nothing is authenticated here — OtpController completes
     * the sign-in once the code checks out.
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

        if ($user !== null) {
            app(ActivityLogger::class)->log('logout', $user, "{$user->name} logged out", logName: 'auth');
        }

        $otp->clear();
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
