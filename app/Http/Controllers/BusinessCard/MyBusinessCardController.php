<?php

declare(strict_types=1);

namespace App\Http\Controllers\BusinessCard;

use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessCard\BusinessCardRequest;
use App\Services\BusinessCard\BusinessCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A registered user managing their own digital business card.
 *
 * Every action reads and writes only $request->user()'s card, so nothing here
 * is permission-gated — the business-cards.* permissions govern the admin
 * module instead.
 */
class MyBusinessCardController extends Controller
{
    public function __construct(private readonly BusinessCardService $service)
    {
    }

    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('city:id,name', 'state:id,name', 'country:id,name');

        return view('my.business-card', [
            // Created on first visit so there is always a card and a link.
            'card' => $this->service->forUser($user),
            'userModel' => $user,
        ]);
    }

    public function update(BusinessCardRequest $request): RedirectResponse
    {
        $card = $this->service->forUser($request->user());

        $data = $this->service->withPhoto(
            $request->validated(),
            $card,
            $request->file('photo'),
            $request->boolean('remove_photo'),
        );
        unset($data['photo']);

        $card->fill($data)->save();

        return redirect()->route('my.business-card.edit')->with('success', 'Business card updated.');
    }

    /**
     * Issue a new public link, revoking every copy already handed out.
     */
    public function regenerate(Request $request): RedirectResponse
    {
        $this->service->regenerateLink($this->service->forUser($request->user()));

        return redirect()->route('my.business-card.edit')
            ->with('success', 'New link generated. The previous link no longer works.');
    }
}
