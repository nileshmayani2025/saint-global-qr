<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PushNotification;
use App\Repositories\Contracts\PushNotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class PushNotificationRepository extends BaseRepository implements PushNotificationRepositoryInterface
{
    protected function model(): string
    {
        return PushNotification::class;
    }

    protected function searchableColumns(): array
    {
        return ['title', 'body'];
    }

    protected function sortableColumns(): array
    {
        return ['title', 'status', 'sent_at', 'created_at', 'updated_at', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['audience'])) {
            $query->where('audience', $filters['audience']);
        }
    }
}
