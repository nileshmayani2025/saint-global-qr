<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\PushNotificationRecipient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The signed-in user's notification inbox — the list behind the topbar bell.
 * Scoped to the caller's own rows throughout, so no permission gates it.
 */
class MyNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $recipients = PushNotificationRecipient::query()
            ->where('user_id', $request->user()->id)
            ->with('pushNotification')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('my.notifications', [
            'recipients' => $recipients,
            'unreadCount' => $request->user()->unreadNotificationCount(),
        ]);
    }

    /**
     * Mark a single notification read, then follow its action link if it has one.
     */
    public function read(Request $request, PushNotificationRecipient $recipient): RedirectResponse
    {
        // Compared as integers: foreign keys arrive as strings from a PDO with
        // emulated prepares, which would 403 a user out of their own row.
        abort_unless((int) $recipient->user_id === (int) $request->user()->id, 403);

        $recipient->forceFill(['read_at' => now()])->save();

        $url = $recipient->pushNotification?->action_url;

        if (filled($url)) {
            return redirect()->to(str_starts_with($url, 'http') ? $url : url($url));
        }

        return redirect()->route('my.notifications');
    }

    public function readAll(Request $request): RedirectResponse
    {
        PushNotificationRecipient::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->route('my.notifications')->with('success', 'All notifications marked as read.');
    }
}
