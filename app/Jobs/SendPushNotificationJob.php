<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PushNotification;
use App\Services\Push\PushNotificationSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Fans a campaign out in the background — a broadcast to every user can mean
 * thousands of individual FCM calls, far past a web request's patience.
 */
class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(public readonly int $pushNotificationId)
    {
    }

    public function handle(PushNotificationSender $sender): void
    {
        $notification = PushNotification::find($this->pushNotificationId);

        if ($notification === null) {
            return;
        }

        $sender->send($notification);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendPushNotificationJob exhausted its retries', [
            'push_notification_id' => $this->pushNotificationId,
            'error' => $e->getMessage(),
        ]);

        PushNotification::where('id', $this->pushNotificationId)->update([
            'status' => PushNotification::STATUS_FAILED,
            'failure_reason' => $e->getMessage(),
        ]);
    }
}
