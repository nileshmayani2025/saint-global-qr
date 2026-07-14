<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Batch;
use App\Repositories\Contracts\BatchRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BatchRepository extends BaseRepository implements BatchRepositoryInterface
{
    protected function model(): string
    {
        return Batch::class;
    }

    protected function searchableColumns(): array
    {
        return ['code'];
    }

    protected function sortableColumns(): array
    {
        return ['code', 'manufacture_date', 'expiry_date', 'quantity', 'created_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        foreach (['company_id', 'product_id'] as $key) {
            if (! empty($filters[$key])) {
                $query->where($key, $filters[$key]);
            }
        }
    }
}
