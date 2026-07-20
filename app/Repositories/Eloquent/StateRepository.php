<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\State;
use App\Repositories\Contracts\StateRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class StateRepository extends BaseRepository implements StateRepositoryInterface
{
    protected function model(): string
    {
        return State::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'code'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'sort_order', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }
    }
}
