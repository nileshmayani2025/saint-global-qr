<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    protected function model(): string
    {
        return Brand::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'slug', 'description'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
    }
}
