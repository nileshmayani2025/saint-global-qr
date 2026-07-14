<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\BatchRequest;
use App\Jobs\GenerateBatchQrCodesJob;
use App\Models\Batch;
use App\Models\Product;
use App\Services\Product\BatchService;
use App\Services\Qr\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Synchronous generation is used up to this size; larger batches are queued.
     */
    private const SYNC_LIMIT = 300;

    public function __construct(private readonly BatchService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Batch::class);

        $filters = $this->filters($request);

        return view('batches.index', [
            'batches' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'created_at'),
                sortDir: (string) $request->string('dir', 'desc'),
                with: ['product'],
            ),
            'filters' => $filters,
            'products' => $this->products($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Batch::class);

        return view('batches.form', [
            'batch' => new Batch(['status' => Batch::STATUS_DRAFT, 'quantity' => 100]),
            'products' => $this->products($request),
        ]);
    }

    public function store(BatchRequest $request): RedirectResponse
    {
        $this->authorize('create', Batch::class);

        $batch = $this->service->create($request->validated());

        return redirect()->route('batches.show', $batch)->with('success', 'Batch created successfully.');
    }

    public function show(Batch $batch): View
    {
        $this->authorize('view', $batch);

        $batch->load('product.brand');

        return view('batches.show', [
            'batch' => $batch,
            'stats' => [
                'generated' => $batch->qr_generated,
                'remaining' => $batch->remainingToGenerate(),
                'verified' => $batch->qrCodes()->where('status', 'verified')->count(),
            ],
        ]);
    }

    public function edit(Request $request, Batch $batch): View
    {
        $this->authorize('update', $batch);

        return view('batches.form', [
            'batch' => $batch,
            'products' => $this->products($request),
        ]);
    }

    public function update(BatchRequest $request, Batch $batch): RedirectResponse
    {
        $this->authorize('update', $batch);

        $this->service->update($batch, $request->validated());

        return redirect()->route('batches.show', $batch)->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch): RedirectResponse
    {
        $this->authorize('delete', $batch);

        $this->service->delete($batch);

        return redirect()->route('batches.index')->with('success', 'Batch deleted successfully.');
    }

    public function generateQr(Request $request, Batch $batch, QrCodeService $qrService): RedirectResponse
    {
        $this->authorize('generateQr', $batch);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $requested = (int) ($validated['quantity'] ?? $batch->remainingToGenerate());
        $requested = min($requested, $batch->remainingToGenerate());

        if ($requested <= 0) {
            return back()->with('error', 'No remaining quantity to generate for this batch.');
        }

        if ($requested <= self::SYNC_LIMIT) {
            $count = $qrService->generateForBatch($batch, $requested);

            return back()->with('success', "Generated {$count} QR codes.");
        }

        GenerateBatchQrCodesJob::dispatch($batch->id, $requested);

        return back()->with('success', "Queued generation of {$requested} QR codes. They will appear shortly.");
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->only(['search', 'status', 'product_id']);

        if ($request->user()->company_id !== null) {
            $filters['company_id'] = $request->user()->company_id;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }

    private function products(Request $request)
    {
        return Product::query()
            ->when($request->user()->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
    }
}
