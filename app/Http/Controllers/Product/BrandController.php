<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\BrandRequest;
use App\Models\Brand;
use App\Services\Product\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(private readonly BrandService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Brand::class);

        $filters = $this->filters($request);

        return view('brands.index', [
            'brands' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'created_at'),
                sortDir: (string) $request->string('dir', 'desc'),
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Brand::class);

        return view('brands.form', ['brand' => new Brand(['status' => 'active'])]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $this->authorize('create', Brand::class);

        $this->service->create($request->validated());

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand): View
    {
        $this->authorize('update', $brand);

        return view('brands.form', ['brand' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorize('update', $brand);

        $this->service->update($brand, $request->validated());

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $this->authorize('delete', $brand);

        $this->service->delete($brand);

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
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
}
