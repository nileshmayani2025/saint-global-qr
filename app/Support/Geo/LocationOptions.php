<?php

declare(strict_types=1);

namespace App\Support\Geo;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Collection;

/**
 * Builds the option lists behind the cascading Country → State → City selects.
 *
 * The whole tree is shipped to the browser in one payload rather than fetched
 * over AJAX: the seeded dataset is India-only (one country, 36 states, a few
 * hundred cities), which is a few kilobytes of JSON and keeps the form working
 * with no extra endpoints and no spinner between the three dropdowns.
 */
final class LocationOptions
{
    /**
     * @return array{countries: Collection, statesByCountry: array<int, list<array{id: int, name: string}>>, citiesByState: array<int, list<array{id: int, name: string}>>}
     */
    public static function all(): array
    {
        return [
            'countries' => Country::query()->active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'statesByCountry' => self::group(
                State::query()->active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'country_id']),
                'country_id',
            ),
            'citiesByState' => self::group(
                City::query()->active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'state_id']),
                'state_id',
            ),
        ];
    }

    /**
     * @return array<int, list<array{id: int, name: string}>>
     */
    private static function group(Collection $rows, string $key): array
    {
        return $rows
            ->groupBy($key)
            ->map(fn (Collection $group) => $group->map(fn ($row) => ['id' => $row->id, 'name' => $row->name])->values()->all())
            ->all();
    }
}
