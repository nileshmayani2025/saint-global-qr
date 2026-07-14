<?php

declare(strict_types=1);

namespace App\Services\Verification;

use App\Models\QrCode;
use App\Models\Scan;
use App\Models\User;
use App\Models\VerificationLog;
use App\Services\Audit\ActivityLogger;
use App\Services\Qr\QrCodeService;
use App\Services\Wallet\WalletService;
use App\Support\RequestContext;
use Illuminate\Support\Facades\DB;

/**
 * Core product-authenticity engine. Resolves a scanned code, applies the fraud
 * rules, records the scan, and — on a first genuine scan — creates the
 * verification log and marks the code verified. Idempotent and race-safe: a
 * unique constraint plus a row lock guarantee a code verifies at most once.
 */
class VerificationService
{
    public function __construct(
        private readonly FraudService $fraud,
        private readonly QrCodeService $qrService,
        private readonly ActivityLogger $logger,
        private readonly WalletService $wallet,
    ) {
    }

    public function verify(ScanContext $context): VerificationResult
    {
        if (app()->bound(RequestContext::class)) {
            $context = $context->withRequestContext(app(RequestContext::class));
        }

        $qrCode = QrCode::query()
            ->with(['product.brand', 'batch', 'company'])
            ->where('code', $context->rawCode)
            ->first();

        // Unknown / forged code.
        if ($qrCode === null) {
            $scan = $this->recordScan($context, null, Scan::RESULT_INVALID);

            return new VerificationResult(
                result: Scan::RESULT_INVALID,
                genuine: false,
                message: 'This code is not recognised. The product may be counterfeit.',
                scan: $scan,
            );
        }

        // Tampered signature.
        if (! $this->qrService->verifySignature($qrCode->code, $qrCode->payload_hash)) {
            $scan = $this->recordScan($context, $qrCode, Scan::RESULT_INVALID, ['signature_mismatch']);

            return new VerificationResult(
                result: Scan::RESULT_INVALID,
                genuine: false,
                message: 'This code failed its integrity check and cannot be trusted.',
                qrCode: $qrCode,
                scan: $scan,
                reasons: ['signature_mismatch'],
            );
        }

        // Blacklisted device / ip / user / code.
        if ($this->fraud->isBlocked($context, $qrCode)) {
            $scan = $this->recordScan($context, $qrCode, Scan::RESULT_BLOCKED, ['blacklisted'], suspected: true);

            return new VerificationResult(
                result: Scan::RESULT_BLOCKED,
                genuine: false,
                message: 'Verification is blocked for this request.',
                qrCode: $qrCode,
                scan: $scan,
                reasons: ['blacklisted'],
            );
        }

        // Administratively blocked code.
        if ($qrCode->isBlocked()) {
            $scan = $this->recordScan($context, $qrCode, Scan::RESULT_BLOCKED, ['code_blocked']);

            return new VerificationResult(
                result: Scan::RESULT_BLOCKED,
                genuine: false,
                message: 'This code has been blocked by the manufacturer.',
                qrCode: $qrCode,
                scan: $scan,
                reasons: ['code_blocked'],
            );
        }

        // Expired batch.
        if ($qrCode->batch?->isExpired()) {
            $scan = $this->recordScan($context, $qrCode, Scan::RESULT_EXPIRED, ['batch_expired']);

            return new VerificationResult(
                result: Scan::RESULT_EXPIRED,
                genuine: false,
                message: 'This product batch has expired.',
                qrCode: $qrCode,
                scan: $scan,
                reasons: ['batch_expired'],
            );
        }

        return $this->attemptGenuineVerification($context, $qrCode);
    }

    private function attemptGenuineVerification(ScanContext $context, QrCode $qrCode): VerificationResult
    {
        $fraudReasons = $this->fraud->assess($context, $qrCode);

        return DB::transaction(function () use ($context, $qrCode, $fraudReasons): VerificationResult {
            // Lock the row so two concurrent first-scans cannot both succeed.
            $locked = QrCode::query()->whereKey($qrCode->getKey())->lockForUpdate()->first();

            if ($locked === null) {
                $scan = $this->recordScan($context, $qrCode, Scan::RESULT_INVALID);

                return new VerificationResult(Scan::RESULT_INVALID, false, 'Code could not be verified.', $qrCode, $scan);
            }

            // Already verified → duplicate scan (no additional reward).
            if ($locked->status === QrCode::STATUS_VERIFIED || $locked->verification()->exists()) {
                $locked->increment('scan_count');
                $scan = $this->recordScan($context, $locked, Scan::RESULT_DUPLICATE, ['already_verified']);

                return new VerificationResult(
                    result: Scan::RESULT_DUPLICATE,
                    genuine: false,
                    message: 'This product is genuine but has already been verified.',
                    qrCode: $locked->loadMissing(['product.brand', 'batch']),
                    scan: $scan,
                    reasons: ['already_verified'],
                );
            }

            $suspected = $fraudReasons !== [];
            $scan = $this->recordScan($context, $locked, Scan::RESULT_VALID, $fraudReasons, $suspected);

            $verification = VerificationLog::create([
                'qr_code_id' => $locked->id,
                'scan_id' => $scan->id,
                'company_id' => $locked->company_id,
                'product_id' => $locked->product_id,
                'batch_id' => $locked->batch_id,
                'user_id' => $context->userId,
                'reward_points' => $locked->reward_points,
                'latitude' => $context->latitude,
                'longitude' => $context->longitude,
                'status' => VerificationLog::STATUS_VERIFIED,
                'verified_at' => now(),
            ]);

            $now = now();
            $locked->forceFill([
                'status' => QrCode::STATUS_VERIFIED,
                'scan_count' => $locked->scan_count + 1,
                'first_scanned_at' => $locked->first_scanned_at ?? $now,
                'verified_at' => $now,
                'activated_at' => $locked->activated_at ?? $now,
            ])->save();

            // Credit the reward wallet when the scan is tied to a known consumer.
            // Idempotent per verification log, so a replay never double-credits.
            if ($context->userId !== null && (int) $locked->reward_points > 0 && $fraudReasons === []) {
                $user = User::find($context->userId);

                if ($user !== null) {
                    $this->wallet->creditRewardForSource(
                        user: $user,
                        amount: (float) $locked->reward_points,
                        source: $verification,
                        description: "Reward for verifying {$locked->product?->name}",
                    );

                    $verification->forceFill(['status' => VerificationLog::STATUS_REWARDED])->save();
                }
            }

            $this->logger->log(
                event: 'verify',
                subject: $locked,
                description: "QR code {$locked->code} verified genuine",
                properties: ['reward_points' => $locked->reward_points, 'fraud' => $fraudReasons],
                logName: 'verification',
                causerId: $context->userId,
            );

            return new VerificationResult(
                result: Scan::RESULT_VALID,
                genuine: true,
                message: 'Congratulations! This product is 100% genuine.',
                qrCode: $locked->loadMissing(['product.brand', 'batch']),
                scan: $scan,
                verification: $verification,
                reasons: $fraudReasons,
            );
        });
    }

    /**
     * @param list<string> $fraudReasons
     */
    private function recordScan(
        ScanContext $context,
        ?QrCode $qrCode,
        string $result,
        array $fraudReasons = [],
        bool $suspected = false,
    ): Scan {
        return Scan::create([
            'qr_code_id' => $qrCode?->id,
            'company_id' => $qrCode?->company_id,
            'user_id' => $context->userId,
            'raw_code' => $context->rawCode,
            'result' => $result,
            'latitude' => $context->latitude,
            'longitude' => $context->longitude,
            'accuracy' => $context->accuracy,
            'ip_address' => $context->ipAddress,
            'user_agent' => $context->userAgent,
            'browser' => $context->browser,
            'device' => $context->device,
            'device_id' => $context->deviceId,
            'is_fraud_suspected' => $suspected || $fraudReasons !== [],
            'fraud_reasons' => $fraudReasons === [] ? null : $fraudReasons,
        ]);
    }
}
