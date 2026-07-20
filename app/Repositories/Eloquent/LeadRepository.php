<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Lead;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class LeadRepository extends BaseRepository implements LeadRepositoryInterface
{
    protected function model(): string
    {
        return Lead::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'phone', 'address', 'remark'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'status', 'created_at', 'updated_at', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (! empty($filters['country_id'])) {
            $query->where('country_id', $filters['country_id']);
        }

        if (! empty($filters['state_id'])) {
            $query->where('state_id', $filters['state_id']);
        }

        if (! empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        // Set by the controller when the caller may only see their own leads.
        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
    }
}
