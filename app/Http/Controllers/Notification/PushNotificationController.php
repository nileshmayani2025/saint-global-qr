<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\PushNotificationRequest;
use App\Jobs\SendPushNotificationJob;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\Push\AudienceResolver;
use App\Services\Push\FirebaseMessaging;
use App\Services\Push\PushNotificationSender;
use App\Support\Access\AccessControl;
use App\Support\Geo\LocationOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PushNotificationController extends Controller
{
    public function __construct(
        private readonly AudienceResolver $audience,
        private readonly PushNotificationSender $sender,
    ) {
    }

    public function index(Request $request, FirebaseMessaging $messaging): View
    {
        $this->authorize('viewAny', PushNotification::class);

        $filters = array_filter(
            $request->only(['search', 'status', 'audience']),
            static fn ($v) => $v !== null && $v !== '',
        );

        $notifications = PushNotification::query()
            ->when(! empty($filters['search']), fn ($q) => $q->where(function ($s) use ($filters): void {
                $s->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('body', 'like', "%{$filters['search']}%");
            }))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['audience']), fn ($q) => $q->where('audience', $filters['audience']))
            ->orderByDesc('created_at')
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return view('push-notifications.index', [
            'notifications' => $notifications,
            'filters' => $filters,
            'audienceResolver' => $this->audience,
            // Surfaced as a banner so a misconfigured server is obvious before
            // someone composes a campaign that silently never delivers.
            'firebaseReady' => $messaging->isConfigured(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', PushNotification::class);

        return view('push-notifications.form', $this->formData(
            new PushNotification(['audience' => PushNotification::AUDIENCE_ALL]),
        ));
    }

    public function store(PushNotificationRequest $request): RedirectResponse
    {
        $this->authorize('create', PushNotification::class);

        $notification = PushNotification::create([
            ...$this->payload($request),
            'status' => PushNotification::STATUS_DRAFT,
        ]);

        // "Save & send" posts send=1; plain "Save" leaves it as a draft.
        if ($request->boolean('send')) {
            return $this->dispatchSend($notification);
        }

        return redirect()->route('push-notifications.index')->with('success', 'Notification saved as a draft.');
    }

    public function show(PushNotification $pushNotification): View
    {
        $this->authorize('view', $pushNotification);

        return view('push-notifications.show', [
            'notification' => $pushNotification,
            'audienceLabel' => $this->audience->describe($pushNotification),
            'readCount' => $pushNotification->recipients()->whereNotNull('read_at')->count(),
        ]);
    }

    public function edit(PushNotification $pushNotification): View
    {
        $this->authorize('update', $pushNotification);

        return view('push-notifications.form', $this->formData($pushNotification));
    }

    public function update(PushNotificationRequest $request, PushNotification $pushNotification): RedirectResponse
    {
        $this->authorize('update', $pushNotification);

        $pushNotification->update($this->payload($request, $pushNotification));

        if ($request->boolean('send')) {
            return $this->dispatchSend($pushNotification);
        }

        return redirect()->route('push-notifications.index')->with('success', 'Notification updated.');
    }

    public function destroy(PushNotification $pushNotification): RedirectResponse
    {
        $this->authorize('delete', $pushNotification);

        $this->sender->forgetImage($pushNotification);
        $pushNotification->delete();

        return redirect()->route('push-notifications.index')->with('success', 'Notification deleted.');
    }

    /**
     * Send (or re-send) an existing campaign.
     */
    public function send(PushNotification $pushNotification): RedirectResponse
    {
        $this->authorize('send', $pushNotification);

        return $this->dispatchSend($pushNotification);
    }

    private function dispatchSend(PushNotification $notification): RedirectResponse
    {
        // The policy's isSendable() check is not enough on its own: Gate::before
        // waves super-admins past every policy, so they could re-send a campaign
        // that has already gone out. Claiming the row with a conditional update
        // enforces it for everyone and also closes the double-click race — only
        // one request can move the status out of draft/failed.
        $claimed = PushNotification::query()
            ->whereKey($notification->id)
            ->whereIn('status', [PushNotification::STATUS_DRAFT, PushNotification::STATUS_FAILED])
            ->update(['status' => PushNotification::STATUS_QUEUED]);

        if ($claimed === 0) {
            return redirect()
                ->route('push-notifications.show', $notification)
                ->with('warning', 'That notification has already been sent or is still sending.');
        }

        SendPushNotificationJob::dispatch($notification->id);

        return redirect()
            ->route('push-notifications.show', $notification)
            ->with('success', 'Notification queued — delivery will finish in the background.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(PushNotificationRequest $request, ?PushNotification $existing = null): array
    {
        $data = [
            'title' => $request->validated('title'),
            'body' => $request->validated('body'),
            'action_url' => $request->validated('action_url'),
            'audience' => $request->validated('audience'),
            'audience_filters' => $request->audienceFilters(),
        ];

        if ($request->hasFile('image')) {
            $existing && $this->sender->forgetImage($existing);
            $data['image_path'] = $request->file('image')->store('notifications', 'public');
        } elseif ($request->boolean('remove_image') && $existing) {
            $this->sender->forgetImage($existing);
            $data['image_path'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(PushNotification $notification): array
    {
        return [
            'notification' => $notification,
            'roles' => AccessControl::roles(),
            'users' => User::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'phone']),
            ...LocationOptions::all(),
        ];
    }
}
