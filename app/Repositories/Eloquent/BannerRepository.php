<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Banner;
use App\Repositories\Contracts\BannerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BannerRepository extends BaseRepository implements BannerRepositoryInterface
{
    protected function model(): string
    {
        return Banner::class;
    }

    protected function searchableColumns(): array
    {
        return ['title', 'subtitle'];
    }

    protected function sortableColumns(): array
    {
        return ['title', 'sort_order', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
    }
}
