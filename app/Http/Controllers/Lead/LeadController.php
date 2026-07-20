<?php

declare(strict_types=1);

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\LeadRequest;
use App\Models\Lead;
use App\Services\Lead\LeadService;
use App\Support\Geo\LocationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $filters = $this->filters($request);

        return view('leads.index', [
            'leads' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'created_at'),
                sortDir: (string) $request->string('dir', 'desc'),
                with: ['city:id,name', 'state:id,name', 'createdBy:id,name'],
            ),
            'filters' => $filters,
            'statuses' => Lead::statuses(),
            'canSeeAll' => $request->user()->can('leads.view-all'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Lead::class);

        return view('leads.form', [
            'lead' => new Lead(['status' => Lead::STATUS_NEW]),
            'statuses' => Lead::statuses(),
            ...LocationOptions::all(),
        ]);
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        $this->service->create($request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead added successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        $lead->load('country:id,name', 'state:id,name', 'city:id,name', 'createdBy:id,name', 'company:id,name');

        return view('leads.show', ['lead' => $lead]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        return view('leads.form', [
            'lead' => $lead,
            'statuses' => Lead::statuses(),
            ...LocationOptions::all(),
        ]);
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        $this->service->update($lead, $request->validated());

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        $this->service->delete($lead);

        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $user = $request->user();
        $filters = $request->only(['search', 'status', 'country_id', 'state_id', 'city_id']);

        if ($user->company_id !== null) {
            $filters['company_id'] = $user->company_id;
        }

        // Without leads.view-all the listing is narrowed to the caller's own
        // leads, matching what LeadPolicy would allow them to open.
        if (! $user->can('leads.view-all')) {
            $filters['created_by'] = $user->id;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }
}
