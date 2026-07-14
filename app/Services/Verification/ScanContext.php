<?php

declare(strict_types=1);

namespace App\Services\Verification;

use App\Support\RequestContext;

/**
 * Immutable description of a single scan attempt: the raw code plus who / where
 * / on what device it was scanned. Passed through the fraud and verification
 * services so they never depend on the HTTP request directly.
 */
final class ScanContext
{
    public function __construct(
        public readonly string $rawCode,
        public readonly ?int $userId = null,
        public readonly ?string $deviceId = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?float $accuracy = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $browser = null,
        public readonly ?string $device = null,
    ) {
    }

    public function withRequestContext(RequestContext $context): self
    {
        return new self(
            rawCode: $this->rawCode,
            userId: $this->userId,
            deviceId: $this->deviceId,
            latitude: $this->latitude,
            longitude: $this->longitude,
            accuracy: $this->accuracy,
            ipAddress: $this->ipAddress ?? $context->ipAddress,
            userAgent: $this->userAgent ?? $context->userAgent,
            browser: $this->browser ?? $context->browser,
            device: $this->device ?? $context->device,
        );
    }
}
