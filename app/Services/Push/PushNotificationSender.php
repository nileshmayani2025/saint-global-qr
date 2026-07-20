<?php

declare(strict_types=1);

namespace App\Services\Push;

use App\Models\PushNotification;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Performs the actual delivery of a campaign: writes an inbox row per targeted
 * user, then pushes to every device token those users hold.
 *
 * The inbox rows are written first and independently of FCM. A user who has
 * never granted notification permission still sees the message under the bell,
 * which is the whole reason the recipients table exists.
 */
class PushNotificationSender
{
    public function __construct(
        private readonly FirebaseMessaging $messaging,
        private readonly AudienceResolver $audience,
    ) {
    }

    public function send(PushNotification $notification): void
    {
        $notification->update(['status' => PushNotification::STATUS_SENDING]);

        try {
            $userIds = $this->fanOutInbox($notification);
            [$sent, $failed] = $this->pushToDevices($notification, $userIds);

            $notification->update([
                'status' => PushNotification::STATUS_SENT,
                'recipient_count' => count($userIds),
                'sent_count' => $sent,
                'failed_count' => $failed,
                'failure_reason' => null,
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Push campaign failed', ['id' => $notification->id, 'error' => $e->getMessage()]);

            $notification->update([
                'status' => PushNotification::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create one inbox row per targeted user.
     *
     * @return list<int>
     */
    private function fanOutInbox(PushNotification $notification): array
    {
        $userIds = [];
        $now = now();

        $this->audience->query($notification)
            ->select('id')
            ->chunkById(500, function ($users) use ($notification, &$userIds, $now): void {
                $rows = $users->map(fn ($user): array => [
                    'push_notification_id' => $notification->id,
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                // insertOrIgnore keeps a retried send idempotent against the
                // unique (notification, user) index.
                DB::table('push_notification_recipients')->insertOrIgnore($rows);

                $userIds = array_merge($userIds, $users->pluck('id')->all());
            });

        return $userIds;
    }

    /**
     * @param  list<int>  $userIds
     * @return array{0: int, 1: int} sent, failed
     */
    private function pushToDevices(PushNotification $notification, array $userIds): array
    {
        if ($userIds === [] || ! $this->messaging->isConfigured()) {
            return [0, 0];
        }

        $sent = 0;
        $failed = 0;
        $dead = [];

        $payload = [
            'url' => $this->resolveUrl($notification),
            'icon' => asset('images/pwa-192.png'),
            'notification_id' => (string) $notification->id,
        ];

        $imageUrl = $notification->image_path ? asset('media/'.$notification->image_path) : null;

        PushSubscription::query()
            ->whereIn('user_id', $userIds)
            ->chunkById(200, function ($subscriptions) use ($notification, $payload, $imageUrl, &$sent, &$failed, &$dead): void {
                foreach ($subscriptions as $subscription) {
                    $result = $this->messaging->sendToToken(
                        $subscription->token,
                        $notification->title,
                        $notification->body,
                        $payload,
                        $imageUrl,
                    );

                    if ($result['ok']) {
                        $sent++;

                        continue;
                    }

                    $failed++;

                    if ($result['dead']) {
                        $dead[] = $subscription->id;
                    }
                }
            });

        // Tokens FCM has rejected as unregistered will never work again.
        if ($dead !== []) {
            PushSubscription::query()->whereIn('id', $dead)->delete();
        }

        return [$sent, $failed];
    }

    private function resolveUrl(PushNotification $notification): string
    {
        $url = $notification->action_url;

        if (blank($url)) {
            return route('my.notifications');
        }

        return str_starts_with($url, 'http') ? $url : url($url);
    }

    /**
     * Delete a campaign's uploaded image, if any.
     */
    public function forgetImage(PushNotification $notification): void
    {
        if ($notification->image_path) {
            Storage::disk('public')->delete($notification->image_path);
        }
    }
}
