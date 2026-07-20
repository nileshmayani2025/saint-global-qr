<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives the FCM registration token the browser mints after the user grants
 * notification permission. Called by resources/views/partials/push.blade.php.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        $token = $validated['token'];

        // A token can migrate between accounts on a shared device, so the hash
        // is the identity and user_id is simply re-pointed.
        PushSubscription::updateOrCreate(
            ['token_hash' => PushSubscription::hash($token)],
            [
                'user_id' => $request->user()->id,
                'token' => $token,
                'user_agent' => substr((string) $request->userAgent(), 0, 512),
                'last_used_at' => now(),
            ],
        );

        return response()->json(['status' => 'subscribed']);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('token_hash', PushSubscription::hash($validated['token']))
            ->delete();

        return response()->json(['status' => 'unsubscribed']);
    }
}
