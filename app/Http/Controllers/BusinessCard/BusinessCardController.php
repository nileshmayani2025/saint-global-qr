<?php

declare(strict_types=1);

namespace App\Http\Controllers\BusinessCard;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use App\Services\BusinessCard\BusinessCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin oversight of every registered person's card.
 *
 * Deliberately read-plus-moderate rather than full edit: the content belongs to
 * the card holder, so an admin can deactivate, re-issue a leaked link or delete
 * a card, but not rewrite someone's details behind their back.
 */
class BusinessCardController extends Controller
{
    public function __construct(private readonly BusinessCardService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BusinessCard::class);

        $filters = $this->filters($request);

        return view('business-cards.index', [
            'cards' => $this->service->paginate(
                filters: $filters,
                perPage: (int) $request->integer('per_page', 15),
                sortBy: (string) $request->string('sort', 'created_at'),
                sortDir: (string) $request->string('dir', 'desc'),
                with: ['user:id,uuid,name,phone,status,company_id'],
            ),
            'filters' => $filters,
        ]);
    }

    /**
     * Flip a card between active and inactive.
     */
    public function toggle(BusinessCard $businessCard): RedirectResponse
    {
        $this->authorize('update', $businessCard);

        $businessCard->forceFill([
            'status' => $businessCard->isActive() ? 'inactive' : 'active',
        ])->save();

        return back()->with('success', $businessCard->isActive()
            ? 'Card is live again.'
            : 'Card has been taken offline.');
    }

    /**
     * Re-issue the public link — used when a card has been shared somewhere the
     * holder did not intend.
     */
    public function regenerate(BusinessCard $businessCard): RedirectResponse
    {
        $this->authorize('update', $businessCard);

        $this->service->regenerateLink($businessCard);

        return back()->with('success', 'New link issued. The previous link no longer works.');
    }

    public function destroy(BusinessCard $businessCard): RedirectResponse
    {
        $this->authorize('delete', $businessCard);

        $this->service->forgetPhoto($businessCard);
        $businessCard->delete();

        return redirect()->route('business-cards.index')->with('success', 'Business card deleted.');
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
