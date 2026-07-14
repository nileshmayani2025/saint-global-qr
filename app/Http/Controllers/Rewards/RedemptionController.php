<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rewards;

use App\Http\Controllers\Controller;
use App\Models\RedemptionRequest;
use App\Services\Reward\RedemptionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Admin-side management of consumer redemption requests.
 */
class RedemptionController extends Controller
{
    public function __construct(private readonly RedemptionService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RedemptionRequest::class);

        $companyId = $request->user()->company_id;
        $status = $request->string('status')->toString();
        $search = trim((string) $request->string('search'));

        $requests = RedemptionRequest::query()
            ->with('user:id,name,email,phone')
            ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
            ->when($status !== '', fn (Builder $q) => $q->where('status', $status))
            ->when($search !== '', fn (Builder $q) => $q->where('reference', 'like', "%{$search}%")
                ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'pending' => RedemptionRequest::query()->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))->where('status', 'pending')->count(),
            'approved' => RedemptionRequest::query()->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))->where('status', 'approved')->count(),
        ];

        return view('redemptions.index', [
            'requests' => $requests,
            'filters' => ['status' => $status, 'search' => $search],
            'counts' => $counts,
        ]);
    }

    public function show(RedemptionRequest $redemption): View
    {
        $this->authorize('view', $redemption);

        $redemption->load(['user:id,name,email,phone', 'reviewer:id,name', 'transaction', 'wallet']);

        return view('redemptions.show', ['redemption' => $redemption]);
    }

    public function approve(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $this->authorize('approve', $redemption);

        $validated = $request->validate([
            'attachment' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:4096'],
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        $attachmentPath = $request->hasFile('attachment')
            ? $request->file('attachment')->store('redemptions', 'public')
            : null;

        try {
            $this->service->approve(
                request: $redemption,
                admin: $request->user(),
                attachmentPath: $attachmentPath,
                reviewNote: $validated['review_note'] ?? null,
            );
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('redemptions.show', $redemption)
            ->with('success', 'Request approved and points debited.');
    }

    public function reject(Request $request, RedemptionRequest $redemption): RedirectResponse
    {
        $this->authorize('reject', $redemption);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $this->service->reject($redemption, $request->user(), $validated['rejection_reason']);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('redemptions.show', $redemption)->with('success', 'Request rejected.');
    }
}
