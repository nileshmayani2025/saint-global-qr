<?php

declare(strict_types=1);

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Marketing\BannerRequest;
use App\Models\Banner;
use App\Services\Marketing\BannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Banner::class);

        $filters = $this->filters($request);

        return view('banners.index', [
            'banners' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'sort_order'),
                sortDir: (string) $request->string('dir', 'asc'),
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Banner::class);

        return view('banners.form', [
            'banner' => new Banner(['status' => 'active', 'sort_order' => 0, 'button_label' => 'Avail Now']),
        ]);
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        $this->authorize('create', Banner::class);

        $this->service->create($this->withImage($request, $request->validated()));

        return redirect()->route('banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        $this->authorize('update', $banner);

        return view('banners.form', ['banner' => $banner]);
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->authorize('update', $banner);

        $this->service->update($banner, $this->withImage($request, $request->validated(), $banner));

        return redirect()->route('banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->authorize('delete', $banner);

        $this->service->delete($banner);

        return redirect()->route('banners.index')->with('success', 'Banner deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->only(['search', 'status']);

        if ($request->user()->company_id !== null) {
            $filters['company_id'] = $request->user()->company_id;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Apply an uploaded/removed banner image to the payload, deleting the old
     * file when it is replaced or explicitly removed.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withImage(Request $request, array $data, ?Banner $banner = null): array
    {
        $old = $banner?->image_path;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('banners', 'public');

            if ($old) {
                Storage::disk('public')->delete($old);
            }
        } elseif ($request->boolean('remove_image') && $old) {
            Storage::disk('public')->delete($old);
            $data['image_path'] = null;
        }

        unset($data['image']);

        return $data;
    }
}
