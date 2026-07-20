<?php

declare(strict_types=1);

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\CityRequest;
use App\Models\City;
use App\Models\State;
use App\Services\Geo\CityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CityController extends Controller
{
    public function __construct(private readonly CityService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', City::class);

        $filters = $this->filters($request);

        return view('cities.index', [
            'cities' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'name'),
                sortDir: (string) $request->string('dir', 'asc'),
                with: ['state:id,name,country_id', 'state.country:id,name'],
            ),
            'filters' => $filters,
            'states' => $this->states(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', City::class);

        return view('cities.form', [
            'city' => new City(['status' => 'active', 'sort_order' => 0]),
            'states' => $this->states(),
        ]);
    }

    public function store(CityRequest $request): RedirectResponse
    {
        $this->authorize('create', City::class);

        $this->service->create($request->validated());

        return redirect()->route('cities.index')->with('success', 'City created successfully.');
    }

    public function edit(City $city): View
    {
        $this->authorize('update', $city);

        return view('cities.form', [
            'city' => $city,
            'states' => $this->states(),
        ]);
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $this->authorize('update', $city);

        $this->service->update($city, $request->validated());

        return redirect()->route('cities.index')->with('success', 'City updated successfully.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $this->authorize('delete', $city);

        $this->service->delete($city);

        return redirect()->route('cities.index')->with('success', 'City deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return array_filter(
            $request->only(['search', 'status', 'state_id']),
            static fn ($v) => $v !== null && $v !== '',
        );
    }

    /**
     * States listed with their country so the dropdown stays unambiguous when
     * two countries have a state of the same name.
     */
    private function states(): Collection
    {
        return State::query()
            ->with('country:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'country_id']);
    }
}
