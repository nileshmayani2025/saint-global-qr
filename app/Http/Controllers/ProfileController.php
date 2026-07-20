<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\SupportContactRequest;
use App\Models\User;
use App\Support\Geo\LocationOptions;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Self-service profile. Every authenticated account can reach this — it only
 * ever reads and writes $request->user(), so no policy or permission applies.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('company:id,name', 'country:id,name', 'state:id,name', 'city:id,name');

        $deviceCount = $user->pushSubscriptions()->count();

        return view('profile.edit', [
            'userModel' => $user,
            'pushEnabled' => $deviceCount > 0,
            'pushDeviceCount' => $deviceCount,
            // Site-wide support numbers, editable here by whoever may manage
            // settings. Falls back to the .env values until overridden.
            'canManageSettings' => $user->can('settings.update'),
            'supportHelpline' => Settings::get('support.helpline', config('support.helpline')),
            'supportWhatsapp' => Settings::get('support.whatsapp', config('support.whatsapp')),
            'supportMessage' => Settings::get('support.whatsapp_message', config('support.whatsapp_message')),
            ...LocationOptions::all(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Role, status and company are intentionally absent from the payload —
        // a user must not be able to promote or reactivate themselves.
        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $this->applyAvatar($request, $user);

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Save the site-wide helpline / WhatsApp numbers shown on every page.
     *
     * These are not personal fields — they live on this screen only because it
     * is where an admin looks for them. SupportContactRequest::authorize()
     * gates it on settings.update.
     */
    public function updateSupportContacts(SupportContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Settings::put([
            'support.helpline' => $data['helpline'] ?? null,
            'support.whatsapp' => $data['whatsapp'] ?? null,
            'support.whatsapp_message' => $data['whatsapp_message'] ?? null,
        ]);

        return redirect()->route('profile.edit')->with('success', 'Support contact numbers updated.');
    }

    /**
     * Store a newly uploaded avatar (or drop the current one), cleaning up the
     * file it replaces so the public disk does not accumulate orphans.
     */
    private function applyAvatar(ProfileRequest $request, User $user): void
    {
        $old = $user->getOriginal('avatar_path');

        if ($request->hasFile('avatar')) {
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $user->avatar_path = null;
        } else {
            return;
        }

        if ($old) {
            Storage::disk('public')->delete($old);
        }
    }
}
