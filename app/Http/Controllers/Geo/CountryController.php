<?php

declare(strict_types=1);

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\CountryRequest;
use App\Models\Country;
use App\Services\Geo\CountryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function __construct(private readonly CountryService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Country::class);

        $filters = $this->filters($request);

        return view('countries.index', [
            'countries' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'name'),
                sortDir: (string) $request->string('dir', 'asc'),
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Country::class);

        return view('countries.form', ['country' => new Country(['status' => 'active', 'sort_order' => 0])]);
    }

    public function store(CountryRequest $request): RedirectResponse
    {
        $this->authorize('create', Country::class);

        $this->service->create($request->validated());

        return redirect()->route('countries.index')->with('success', 'Country created successfully.');
    }

    public function edit(Country $country): View
    {
        $this->authorize('update', $country);

        return view('countries.form', ['country' => $country]);
    }

    public function update(CountryRequest $request, Country $country): RedirectResponse
    {
        $this->authorize('update', $country);

        $this->service->update($country, $request->validated());

        return redirect()->route('countries.index')->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $this->authorize('delete', $country);

        $this->service->delete($country);

        return redirect()->route('countries.index')->with('success', 'Country deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return array_filter(
            $request->only(['search', 'status']),
            static fn ($v) => $v !== null && $v !== '',
        );
    }
}
