<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\City;
use App\Repositories\Contracts\CityRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class CityRepository extends BaseRepository implements CityRepositoryInterface
{
    protected function model(): string
    {
        return City::class;
    }

    protected function searchableColumns(): array
    {
        return ['name'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'sort_order', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['state_id'])) {
            $query->where('state_id', $filters['state_id']);
        }

        if (! empty($filters['country_id'])) {
            $query->whereHas('state', fn (Builder $q) => $q->where('country_id', $filters['country_id']));
        }
    }
}
