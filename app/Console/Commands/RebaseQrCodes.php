<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\QrCode;
use App\Services\Qr\QrCodeService;
use Illuminate\Console\Command;

/**
 * Re-point every QR code at the current APP_URL and re-render its PNG.
 *
 * QR codes bake an absolute verify URL captured at generation time, so codes
 * built against one host (e.g. a dev machine's IP, or the domain root before the
 * app moved under /qr/public) 404 when scanned. Run this on the live server —
 * after confirming APP_URL — to fix the stored short_url and the images:
 *
 *     php artisan qr:rebase
 */
class RebaseQrCodes extends Command
{
    protected $signature = 'qr:rebase {--chunk=200 : Rows processed per batch}';

    protected $description = 'Re-point every QR code at the current APP_URL and re-render its image';

    public function handle(QrCodeService $service): int
    {
        $base = rtrim((string) config('app.url'), '/');
        $this->info("Rebasing QR codes to {$base}/verify/{code}");

        $changed = 0;
        $seen = 0;

        QrCode::query()
            ->orderBy('id')
            ->chunkById((int) $this->option('chunk'), function ($codes) use ($service, &$changed, &$seen): void {
                foreach ($codes as $qr) {
                    $seen++;
                    if ($service->rebase($qr)) {
                        $changed++;
                    }
                }
                $this->getOutput()->write('.');
            });

        $this->newLine();
        $this->info("Done — {$changed} of {$seen} codes updated.");

        return self::SUCCESS;
    }
}
