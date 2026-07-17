<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Support\Phone;
use Illuminate\Contracts\Session\Session;

/**
 * Holds the pending mobile-number challenge between the "enter your number"
 * screen and the OTP screen.
 *
 * NOTE ON SECURITY: no SMS gateway is configured, so the code is never
 * delivered anywhere — it is simply the last 4 digits of the number being
 * verified, and the OTP screen pre-fills it. This confirms the number was
 * typed correctly; it does NOT prove the person holds the SIM, so anyone who
 * knows a number can sign in as its owner. To make this a real second factor,
 * change codeFor() to a random code and send it over SMS.
 */
final class OtpService
{
    public const INTENT_LOGIN = 'login';

    public const INTENT_REGISTER = 'register';

    /** Digits in the code — must stay 4 while the code is the number's tail. */
    public const LENGTH = 4;

    private const SESSION_KEY = 'auth.otp';

    private const TTL_SECONDS = 600;

    private const MAX_ATTEMPTS = 5;

    public function __construct(private readonly Session $session)
    {
    }

    /**
     * Open a challenge for $phone and remember what to do once it passes.
     * $name is carried for the register intent, where no user row exists yet.
     */
    public function start(string $phone, string $intent, ?string $name = null): void
    {
        $this->session->put(self::SESSION_KEY, [
            'phone' => Phone::normalize($phone),
            'intent' => $intent,
            'name' => $name,
            'expires_at' => now()->addSeconds(self::TTL_SECONDS)->timestamp,
            'attempts' => 0,
        ]);
    }

    /**
     * The pending challenge, or null when there is none or it has expired.
     *
     * @return array{phone: string, intent: string, name: ?string, expires_at: int, attempts: int}|null
     */
    public function challenge(): ?array
    {
        $challenge = $this->session->get(self::SESSION_KEY);

        if (! is_array($challenge) || ! isset($challenge['phone'], $challenge['intent'], $challenge['expires_at'])) {
            return null;
        }

        if (now()->timestamp >= (int) $challenge['expires_at']) {
            $this->clear();

            return null;
        }

        return $challenge;
    }

    /** The code the OTP screen expects — the last 4 digits of the number. */
    public function codeFor(string $phone): string
    {
        return substr(Phone::normalize($phone), -self::LENGTH);
    }

    /**
     * Check $code against the pending challenge. Each call burns an attempt, so
     * a wrong code can't be retried indefinitely inside one challenge window.
     */
    public function verify(string $code): bool
    {
        $challenge = $this->challenge();

        if ($challenge === null || $challenge['attempts'] >= self::MAX_ATTEMPTS) {
            $this->clear();

            return false;
        }

        $this->session->put(self::SESSION_KEY, [
            ...$challenge,
            'attempts' => $challenge['attempts'] + 1,
        ]);

        return hash_equals(
            $this->codeFor($challenge['phone']),
            (string) preg_replace('/\D+/', '', $code),
        );
    }

    /** Restart the timer and attempt counter for the same number. */
    public function resend(): bool
    {
        $challenge = $this->challenge();

        if ($challenge === null) {
            return false;
        }

        $this->start($challenge['phone'], $challenge['intent'], $challenge['name']);

        return true;
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }
}
