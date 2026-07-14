<?php

declare(strict_types=1);

namespace App\Http\Controllers\Qr;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\QrCode;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use App\Services\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrCodeController extends Controller
{
    public function __construct(private readonly QrCodeRepositoryInterface $repository)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', QrCode::class);

        $filters = $request->only(['search', 'status', 'batch_id', 'product_id']);

        if ($request->user()->company_id !== null) {
            $filters['company_id'] = $request->user()->company_id;
        }

        $filters = array_filter($filters, static fn ($v) => $v !== null && $v !== '');

        return view('qr-codes.index', [
            'qrCodes' => $this->repository->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 24),
                sortBy: (string) $request->string('sort', 'created_at'),
                sortDir: (string) $request->string('dir', 'desc'),
                with: ['product', 'batch'],
            ),
            'filters' => $filters,
        ]);
    }

    public function show(QrCode $qrCode): View
    {
        $this->authorize('view', $qrCode);

        $qrCode->load(['product.brand', 'batch', 'verification']);
        $scans = $qrCode->scans()->latest()->limit(20)->get();

        return view('qr-codes.show', compact('qrCode', 'scans'));
    }

    public function printSheet(Request $request, Batch $batch): View
    {
        abort_unless($request->user()->can('qr-codes.print'), 403);
        abort_unless(
            $request->user()->company_id === null || $batch->company_id === $request->user()->company_id,
            403,
        );

        $qrCodes = $batch->qrCodes()->with('product')->orderBy('serial')->get();

        return view('qr-codes.print', compact('batch', 'qrCodes'));
    }

    public function block(Request $request, QrCode $qrCode, ActivityLogger $logger): RedirectResponse
    {
        $this->authorize('block', $qrCode);

        $qrCode->forceFill(['status' => QrCode::STATUS_BLOCKED])->save();

        $logger->log('qr_blocked', $qrCode, "QR code {$qrCode->code} blocked", logName: 'qr');

        return back()->with('success', 'QR code blocked.');
    }
}
