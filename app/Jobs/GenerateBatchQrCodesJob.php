<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Batch;
use App\Services\Qr\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Generates a batch's QR codes in the background, chunked so a large batch never
 * holds one enormous transaction or exhausts memory rendering images.
 */
class GenerateBatchQrCodesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 3600;
    public int $tries = 3;

    private const CHUNK = 250;

    public function __construct(
        public readonly int $batchId,
        public readonly ?int $quantity = null,
    ) {
    }

    public function handle(QrCodeService $service): void
    {
        $batch = Batch::query()->find($this->batchId);

        if ($batch === null) {
            return;
        }

        $target = $this->quantity ?? $batch->remainingToGenerate();
        $produced = 0;

        while ($produced < $target && $batch->remainingToGenerate() > 0) {
            $chunk = min(self::CHUNK, $target - $produced);
            $made = $service->generateForBatch($batch->refresh(), $chunk);

            if ($made === 0) {
                break;
            }

            $produced += $made;
        }
    }
}
