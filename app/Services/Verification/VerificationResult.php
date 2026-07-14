<?php

declare(strict_types=1);

namespace App\Services\Verification;

use App\Models\QrCode;
use App\Models\Scan;
use App\Models\VerificationLog;

/**
 * Outcome of a verification attempt, safe to expose to the public verify page
 * and the API. `genuine` is true only on a first successful verification.
 */
final class VerificationResult
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        public readonly string $result,           // Scan::RESULT_*
        public readonly bool $genuine,
        public readonly string $message,
        public readonly ?QrCode $qrCode = null,
        public readonly ?Scan $scan = null,
        public readonly ?VerificationLog $verification = null,
        public readonly array $reasons = [],
    ) {
    }

    public function isValid(): bool
    {
        return $this->result === Scan::RESULT_VALID;
    }

    public function isDuplicate(): bool
    {
        return $this->result === Scan::RESULT_DUPLICATE;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $product = $this->qrCode?->relationLoaded('product')
            ? $this->qrCode->product
            : $this->qrCode?->product;

        return [
            'result' => $this->result,
            'genuine' => $this->genuine,
            'message' => $this->message,
            'reasons' => $this->reasons,
            'reward_points' => $this->verification?->reward_points,
            'product' => $product ? [
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->brand?->name,
            ] : null,
            'batch' => $this->qrCode?->batch ? [
                'code' => $this->qrCode->batch->code,
                'manufacture_date' => optional($this->qrCode->batch->manufacture_date)->toDateString(),
                'expiry_date' => optional($this->qrCode->batch->expiry_date)->toDateString(),
            ] : null,
            'verified_at' => optional($this->verification?->verified_at)->toIso8601String(),
        ];
    }
}
