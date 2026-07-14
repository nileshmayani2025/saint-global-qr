<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $filters = $this->filters($request);

        $products = $this->service->paginate(
            filters: $filters,
            perPage: (int) $request->integer('per_page', 15),
            sortBy: (string) $request->string('sort', 'created_at'),
            sortDir: (string) $request->string('dir', 'desc'),
            with: ['brand', 'category'],
        );

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'brands' => $this->brands($request),
            'categories' => $this->categories($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Product::class);

        return view('products.form', [
            'product' => new Product(['status' => 'active', 'unit' => 'piece', 'reward_points' => 0, 'mrp' => 0]),
            'brands' => $this->brands($request),
            'categories' => $this->categories($request),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $this->withImage($request, $request->safe()->except(['image', 'remove_image']));

        $product = $this->service->create($data);

        return $this->redirectAfterSave($request, $product, 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        $product->load(['brand', 'category', 'batches' => fn ($q) => $q->latest()->limit(10)]);

        return view('products.show', [
            'product' => $product,
            'stats' => [
                'batches' => $product->batches()->count(),
                'qr_codes' => $product->qrCodes()->count(),
                'verified' => $product->qrCodes()->where('status', 'verified')->count(),
            ],
        ]);
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('update', $product);

        return view('products.form', [
            'product' => $product,
            'brands' => $this->brands($request),
            'categories' => $this->categories($request),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $this->withImage($request, $request->safe()->except(['image', 'remove_image']), $product);

        $this->service->update($product, $data);

        return $this->redirectAfterSave($request, $product, 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->service->delete($product);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->only(['search', 'brand_id', 'category_id', 'status']);

        if ($request->user()->company_id !== null) {
            $filters['company_id'] = $request->user()->company_id;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Apply an uploaded/removed product image to the payload, deleting the old
     * file when it is replaced or explicitly removed.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withImage(Request $request, array $data, ?Product $product = null): array
    {
        $old = $product?->image_path;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');

            if ($old) {
                Storage::disk('public')->delete($old);
            }
        } elseif ($request->boolean('remove_image') && $old) {
            Storage::disk('public')->delete($old);
            $data['image_path'] = null;
        }

        return $data;
    }

    private function redirectAfterSave(Request $request, Product $product, string $message): RedirectResponse
    {
        if ($request->input('after_save') === 'continue') {
            return redirect()->route('products.edit', $product)->with('success', $message);
        }

        return redirect()->route('products.index')->with('success', $message);
    }

    private function brands(Request $request)
    {
        return Brand::query()
            ->when($request->user()->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function categories(Request $request)
    {
        return Category::query()
            ->when($request->user()->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
