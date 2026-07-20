<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PushNotification;
use App\Models\User;

class PushNotificationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('notifications.view');
    }

    public function view(User $user, PushNotification $notification): bool
    {
        return $user->can('notifications.view');
    }

    public function create(User $user): bool
    {
        return $user->can('notifications.create');
    }

    public function update(User $user, PushNotification $notification): bool
    {
        // A campaign that has already gone out is an audit record, not a draft.
        return $user->can('notifications.update') && ! $notification->isSent();
    }

    public function delete(User $user, PushNotification $notification): bool
    {
        return $user->can('notifications.delete');
    }

    /**
     * Sending is separated from creating so a junior user can draft a campaign
     * while only a senior one may actually broadcast it.
     */
    public function send(User $user, PushNotification $notification): bool
    {
        return $user->can('notifications.send') && $notification->isSendable();
    }
}
