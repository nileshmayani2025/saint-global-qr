<?php

declare(strict_types=1);

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lead\AppLeadRequest;
use App\Models\Company;
use App\Models\Lead;
use App\Support\Geo\LocationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lead capture from the app: a karigar meets a customer and takes their number.
 *
 * Scoped entirely to the caller's own leads, so no permission gates it — the
 * leads.* permissions still govern the staff module at /leads, where the whole
 * company pipeline lives.
 */
class MyLeadController extends Controller
{
    public function index(Request $request): View
    {
        return view('my.leads', [
            'leads' => Lead::query()
                ->where('created_by', $request->user()->id)
                ->with('city:id,name', 'state:id,name')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('my.lead-form', [
            'lead' => new Lead,
            ...LocationOptions::all(),
        ]);
    }

    public function store(AppLeadRequest $request): RedirectResponse
    {
        $user = $request->user();

        Lead::create([
            ...$request->validated(),
            // App users are usually consumers with no company of their own, so
            // fall back to the first company — otherwise the lead would be
            // invisible to every company-scoped admin.
            'company_id' => $user->company_id ?? Company::query()->orderBy('id')->value('id'),
            'status' => Lead::STATUS_NEW,
        ]);

        return redirect()->route('my.leads.index')->with('success', 'Lead added. Thank you!');
    }
}
