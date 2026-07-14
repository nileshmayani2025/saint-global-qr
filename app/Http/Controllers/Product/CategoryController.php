<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CategoryRequest;
use App\Models\Category;
use App\Services\Product\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Category::class);

        $filters = $this->filters($request);

        return view('categories.index', [
            'categories' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'sort_order'),
                sortDir: (string) $request->string('dir', 'asc'),
                with: ['parent'],
            ),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Category::class);

        return view('categories.form', [
            'category' => new Category(['status' => 'active', 'sort_order' => 0]),
            'parents' => $this->parents($request),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->service->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Request $request, Category $category): View
    {
        $this->authorize('update', $category);

        return view('categories.form', [
            'category' => $category,
            'parents' => $this->parents($request)->where('id', '!=', $category->id),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->service->update($category, $request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->service->delete($category);

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
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

    private function parents(Request $request)
    {
        return Category::query()
            ->when($request->user()->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
