<?php

declare(strict_types=1);

namespace App\Services\Qr;

use App\Models\Batch;
use App\Models\QrCode as QrCodeModel;
use App\Services\Audit\ActivityLogger;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Generates secure, signed, printable QR codes for a batch. Each code carries a
 * random token plus an HMAC signature so a verification server can detect forged
 * or tampered codes without a database round-trip.
 */
class QrCodeService
{
    private const DISK = 'public';
    private const MAX_TOKEN_RETRIES = 5;

    public function __construct(private readonly ActivityLogger $logger)
    {
    }

    /**
     * Generate up to $quantity codes for the batch (defaults to the batch's
     * remaining planned quantity). Returns the number actually generated.
     */
    public function generateForBatch(Batch $batch, ?int $quantity = null): int
    {
        $remaining = $batch->remainingToGenerate();
        $toGenerate = $quantity === null ? $remaining : min($quantity, $remaining);

        if ($toGenerate <= 0) {
            return 0;
        }

        $batch->loadMissing('product');
        $rewardPoints = $batch->effectiveRewardPoints();

        $generated = DB::transaction(function () use ($batch, $toGenerate, $rewardPoints): int {
            $count = 0;

            for ($i = 0; $i < $toGenerate; $i++) {
                $serial = $batch->qr_generated + $i + 1;
                $code = $this->uniqueToken();

                $qr = new QrCodeModel([
                    'company_id' => $batch->company_id,
                    'batch_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'code' => $code,
                    'serial' => $serial,
                    'payload_hash' => $this->sign($code),
                    'reward_points' => $rewardPoints,
                    'short_url' => $this->verifyUrl($code),
                    'status' => QrCodeModel::STATUS_GENERATED,
                ]);
                $qr->save();

                $qr->image_path = $this->renderImage($qr);
                $qr->saveQuietly();

                $count++;
            }

            $batch->qr_generated += $count;
            if ($batch->status === Batch::STATUS_DRAFT || $batch->status === Batch::STATUS_GENERATING) {
                $batch->status = $batch->remainingToGenerate() > 0
                    ? Batch::STATUS_GENERATING
                    : Batch::STATUS_ACTIVE;
            }
            $batch->save();

            return $count;
        });

        $this->logger->log(
            event: 'qr_generated',
            subject: $batch,
            description: "Generated {$generated} QR codes for batch {$batch->code}",
            properties: ['count' => $generated],
            logName: 'qr',
        );

        return $generated;
    }

    /**
     * HMAC signature binding a code to this application's key.
     */
    public function sign(string $code): string
    {
        return hash_hmac('sha256', $code, $this->secret());
    }

    /**
     * Constant-time verification that a code's signature is authentic.
     */
    public function verifySignature(string $code, string $hash): bool
    {
        return hash_equals($this->sign($code), $hash);
    }

    public function verifyUrl(string $code): string
    {
        return rtrim((string) config('app.url'), '/')."/verify/{$code}";
    }

    /**
     * Re-point an existing code at the current APP_URL and re-render its image.
     * Run after APP_URL changes — e.g. codes generated against a dev host now
     * served from the live domain — so both the stored short_url and the printed
     * PNG resolve. Returns true when the code's URL actually changed.
     */
    public function rebase(QrCodeModel $qr): bool
    {
        $fresh = $this->verifyUrl($qr->code);

        if ($qr->short_url === $fresh) {
            return false;
        }

        $qr->short_url = $fresh;
        $qr->image_path = $this->renderImage($qr);
        $qr->saveQuietly();

        return true;
    }

    /**
     * Render the QR PNG to the public disk and return its relative path.
     */
    private function renderImage(QrCodeModel $qr): string
    {
        $qrCode = new QrCode(
            data: $qr->short_url ?? $this->verifyUrl($qr->code),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 12,
        );

        $result = (new PngWriter())->write($qrCode);

        $path = "qr/{$qr->company_id}/{$qr->batch_id}/{$qr->code}.png";
        Storage::disk(self::DISK)->put($path, $result->getString());

        return $path;
    }

    private function uniqueToken(): string
    {
        for ($attempt = 0; $attempt < self::MAX_TOKEN_RETRIES; $attempt++) {
            $token = strtoupper(bin2hex(random_bytes(12))); // 24 hex chars

            if (! QrCodeModel::query()->where('code', $token)->exists()) {
                return $token;
            }
        }

        throw new RuntimeException('Unable to generate a unique QR token after multiple attempts.');
    }

    private function secret(): string
    {
        $key = (string) config('app.key');

        return str_starts_with($key, 'base64:')
            ? base64_decode(substr($key, 7))
            : $key;
    }
}
