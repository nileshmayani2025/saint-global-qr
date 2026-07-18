<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Services\Auth\OtpService;
use App\Support\Access\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Step 2 of both sign-in and sign-up: verify the code, then either log the
 * existing account in or create the new one. See OtpService for why the code
 * is the number's last 4 digits and is pre-filled rather than sent by SMS.
 */
class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otp)
    {
    }

    public function show(): View|RedirectResponse
    {
        $challenge = $this->otp->challenge();

        if ($challenge === null) {
            return $this->restart();
        }

        return view('auth.otp', [
            'phone' => $challenge['phone'],
            'intent' => $challenge['intent'],
            'prefill' => $this->otp->codeFor($challenge['phone']),
        ]);
    }

    public function verify(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $challenge = $this->otp->challenge();

        if ($challenge === null) {
            return $this->restart();
        }

        if (! $this->otp->verify($request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => __('That code is incorrect. Please try again.'),
            ]);
        }

        $user = $challenge['intent'] === OtpService::INTENT_REGISTER
            ? $this->createAccount($challenge['phone'], (string) $challenge['name'], $logger)
            : $this->activeAccountFor($challenge['phone']);

        $remember = (bool) $request->session()->pull('auth.remember', false);
        $this->otp->clear();

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->forceFill([
            'phone_verified_at' => $user->phone_verified_at ?? now(),
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        $logger->log('login', $user, "{$user->name} logged in", logName: 'auth');

        return redirect()->intended(route('dashboard'));
    }

    public function resend(): RedirectResponse
    {
        if (! $this->otp->resend()) {
            return $this->restart();
        }

        return redirect()->route('otp.show')->with('success', __('A new code is ready.'));
    }

    /** Drop back to the number entry screen when no challenge is pending. */
    private function restart(): RedirectResponse
    {
        $this->otp->clear();

        return redirect()->route('app.login')->with(
            'info',
            __('Your code expired. Please enter your mobile number again.'),
        );
    }

    private function activeAccountFor(string $phone): User
    {
        $user = User::query()
            ->where('phone', $phone)
            ->where('status', 'active')
            ->first();

        if ($user === null) {
            $this->otp->clear();

            throw ValidationException::withMessages([
                'code' => __('No active account is registered with this mobile number.'),
            ]);
        }

        return $user;
    }

    private function createAccount(string $phone, string $name, ActivityLogger $logger): User
    {
        $user = new User;
        $user->fill([
            'name' => $name,
            'phone' => $phone,
            'company_id' => Company::query()->orderBy('id')->value('id'),
            'status' => 'active',
            // No admin approval step: a new account can scan straight away.
            'approved_at' => now(),
            'phone_verified_at' => now(),
        ]);

        // Sign-in is by OTP only, so no password is ever used. The column is
        // NOT NULL, so store an unguessable value nobody can authenticate with.
        $user->password = Hash::make(Str::random(40));
        $user->save();

        // Self-registered accounts are consumers (karigar) by default.
        $user->assignRole(AccessControl::ROLE_KARIGAR);

        $logger->log('register', $user, "{$user->name} registered", logName: 'auth', causerId: $user->id);

        return $user;
    }
}
