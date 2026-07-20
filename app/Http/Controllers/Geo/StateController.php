<?php

declare(strict_types=1);

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StateRequest;
use App\Models\Country;
use App\Models\State;
use App\Services\Geo\StateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StateController extends Controller
{
    public function __construct(private readonly StateService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', State::class);

        $filters = $this->filters($request);

        return view('states.index', [
            'states' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'name'),
                sortDir: (string) $request->string('dir', 'asc'),
                with: ['country:id,name'],
            ),
            'filters' => $filters,
            'countries' => $this->countries(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', State::class);

        return view('states.form', [
            'state' => new State(['status' => 'active', 'sort_order' => 0]),
            'countries' => $this->countries(),
        ]);
    }

    public function store(StateRequest $request): RedirectResponse
    {
        $this->authorize('create', State::class);

        $this->service->create($request->validated());

        return redirect()->route('states.index')->with('success', 'State created successfully.');
    }

    public function edit(State $state): View
    {
        $this->authorize('update', $state);

        return view('states.form', [
            'state' => $state,
            'countries' => $this->countries(),
        ]);
    }

    public function update(StateRequest $request, State $state): RedirectResponse
    {
        $this->authorize('update', $state);

        $this->service->update($state, $request->validated());

        return redirect()->route('states.index')->with('success', 'State updated successfully.');
    }

    public function destroy(State $state): RedirectResponse
    {
        $this->authorize('delete', $state);

        $this->service->delete($state);

        return redirect()->route('states.index')->with('success', 'State deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        return array_filter(
            $request->only(['search', 'status', 'country_id']),
            static fn ($v) => $v !== null && $v !== '',
        );
    }

    private function countries(): Collection
    {
        return Country::query()->orderBy('name')->get(['id', 'name']);
    }
}
