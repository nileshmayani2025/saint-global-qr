<?php

declare(strict_types=1);

namespace App\Services\Push;

use App\Models\City;
use App\Models\Country;
use App\Models\PushNotification;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Turns a campaign's stored audience selection into a user query.
 *
 * Only active users are ever targeted — a suspended account should not keep
 * receiving marketing pushes.
 */
class AudienceResolver
{
    public function query(PushNotification $notification): Builder
    {
        $filters = $notification->audience_filters ?? [];

        $query = User::query()->where('status', 'active');

        return match ($notification->audience) {
            PushNotification::AUDIENCE_ROLE => $query->whereHas(
                'roles',
                fn (Builder $q) => $q->whereIn('name', $filters['roles'] ?? ['__none__']),
            ),

            PushNotification::AUDIENCE_USERS => $query->whereIn('id', $filters['user_ids'] ?? [0]),

            PushNotification::AUDIENCE_LOCATION => $query
                ->when(! empty($filters['country_id']), fn (Builder $q) => $q->where('country_id', $filters['country_id']))
                ->when(! empty($filters['state_id']), fn (Builder $q) => $q->where('state_id', $filters['state_id']))
                ->when(! empty($filters['city_id']), fn (Builder $q) => $q->where('city_id', $filters['city_id'])),

            default => $query,
        };
    }

    /**
     * Human-readable audience summary for the admin list and detail screens.
     */
    public function describe(PushNotification $notification): string
    {
        $filters = $notification->audience_filters ?? [];

        return match ($notification->audience) {
            PushNotification::AUDIENCE_ROLE => 'Roles: '.(implode(', ', $filters['roles'] ?? []) ?: '—'),
            PushNotification::AUDIENCE_USERS => count($filters['user_ids'] ?? []).' selected user(s)',
            PushNotification::AUDIENCE_LOCATION => $this->describeLocation($filters),
            default => 'All active users',
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function describeLocation(array $filters): string
    {
        $parts = array_filter([
            ! empty($filters['city_id']) ? City::find($filters['city_id'])?->name : null,
            ! empty($filters['state_id']) ? State::find($filters['state_id'])?->name : null,
            ! empty($filters['country_id']) ? Country::find($filters['country_id'])?->name : null,
        ]);

        return $parts === [] ? 'Any location' : implode(', ', $parts);
    }
}
