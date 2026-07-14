<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Immutable snapshot of the current request's audit context (IP, user agent,
 * parsed browser + device, URL and method). Registered as a scoped singleton by
 * the CaptureRequestContext middleware and consumed by the activity logger so
 * every audit row carries who/where/how without threading the Request around.
 */
final class RequestContext
{
    public function __construct(
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $browser = null,
        public readonly ?string $device = null,
        public readonly ?string $url = null,
        public readonly ?string $method = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $agent = (string) $request->userAgent();

        return new self(
            ipAddress: $request->ip(),
            userAgent: $agent !== '' ? $agent : null,
            browser: self::detectBrowser($agent),
            device: self::detectDevice($agent),
            url: $request->fullUrl(),
            method: $request->method(),
        );
    }

    /**
     * Empty context for console / queue execution where no HTTP request exists.
     */
    public static function empty(): self
    {
        return new self();
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'browser' => $this->browser,
            'device' => $this->device,
            'url' => $this->url,
            'method' => $this->method,
        ];
    }

    private static function detectBrowser(string $agent): ?string
    {
        if ($agent === '') {
            return null;
        }

        return match (true) {
            str_contains($agent, 'Edg') => 'Edge',
            str_contains($agent, 'OPR') || str_contains($agent, 'Opera') => 'Opera',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Safari') => 'Safari',
            str_contains($agent, 'MSIE') || str_contains($agent, 'Trident') => 'Internet Explorer',
            default => 'Unknown',
        };
    }

    private static function detectDevice(string $agent): ?string
    {
        if ($agent === '') {
            return null;
        }

        return match (true) {
            str_contains($agent, 'iPad') || str_contains($agent, 'Tablet') => 'Tablet',
            str_contains($agent, 'Mobile') || str_contains($agent, 'Android') || str_contains($agent, 'iPhone') => 'Mobile',
            default => 'Desktop',
        };
    }
}
