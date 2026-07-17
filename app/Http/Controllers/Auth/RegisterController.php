<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    /**
     * Step 1 of sign-up: nothing is written yet. The account is only created
     * once the OTP passes, so an abandoned form leaves no half-made user.
     */
    public function requestOtp(RegisterRequest $request, OtpService $otp): RedirectResponse
    {
        $phone = $request->string('phone')->toString();

        if (User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => __('This mobile number is already registered — please sign in instead.'),
            ]);
        }

        $otp->start($phone, OtpService::INTENT_REGISTER, $request->string('name')->toString());

        return redirect()->route('otp.show');
    }
}
