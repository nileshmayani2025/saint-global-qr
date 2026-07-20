<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\TradingVideoRequest;
use App\Models\Product;
use App\Models\ProductTradingVideo;
use App\Services\Product\TradingVideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TradingVideoController extends Controller
{
    public function __construct(private readonly TradingVideoService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ProductTradingVideo::class);

        $filters = $this->filters($request);

        return view('trading-videos.index', [
            'videos' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'sort_order'),
                sortDir: (string) $request->string('dir', 'asc'),
                with: ['product:id,name,sku'],
            ),
            'filters' => $filters,
            'products' => $this->products($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ProductTradingVideo::class);

        return view('trading-videos.form', [
            'video' => new ProductTradingVideo(['status' => 'active', 'sort_order' => 0]),
            'products' => $this->products($request),
        ]);
    }

    public function store(TradingVideoRequest $request): RedirectResponse
    {
        $this->authorize('create', ProductTradingVideo::class);

        $this->service->create($request->validated());

        return redirect()->route('trading-videos.index')->with('success', 'Trading video added successfully.');
    }

    public function edit(Request $request, ProductTradingVideo $tradingVideo): View
    {
        $this->authorize('update', $tradingVideo);

        return view('trading-videos.form', [
            'video' => $tradingVideo,
            'products' => $this->products($request),
        ]);
    }

    public function update(TradingVideoRequest $request, ProductTradingVideo $tradingVideo): RedirectResponse
    {
        $this->authorize('update', $tradingVideo);

        $this->service->update($tradingVideo, $request->validated());

        return redirect()->route('trading-videos.index')->with('success', 'Trading video updated successfully.');
    }

    public function destroy(ProductTradingVideo $tradingVideo): RedirectResponse
    {
        $this->authorize('delete', $tradingVideo);

        $this->service->delete($tradingVideo);

        return redirect()->route('trading-videos.index')->with('success', 'Trading video deleted successfully.');
    }

    /**
     * Records that the caller has seen this video, so the welcome popup stays
     * closed until a newer one is published.
     *
     * Not authorized against the trading-videos.* permissions on purpose: every
     * signed-in user sees the popup, and this only writes a watermark onto
     * their own row.
     */
    public function markSeen(Request $request, ProductTradingVideo $tradingVideo): JsonResponse
    {
        $user = $request->user();

        // Never move the watermark backwards — an older video being opened
        // must not make a newer one pop up again.
        if ((int) $tradingVideo->id > (int) $user->last_seen_trading_video_id) {
            $user->forceFill(['last_seen_trading_video_id' => $tradingVideo->id])->save();
        }

        return response()->json(['status' => 'ok']);
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

    private function products(Request $request): Collection
    {
        return Product::query()
            ->when($request->user()->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
    }
}
