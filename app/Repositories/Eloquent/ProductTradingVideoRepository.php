<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductTradingVideo;
use App\Repositories\Contracts\ProductTradingVideoRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductTradingVideoRepository extends BaseRepository implements ProductTradingVideoRepositoryInterface
{
    protected function model(): string
    {
        return ProductTradingVideo::class;
    }

    protected function searchableColumns(): array
    {
        return ['title', 'url', 'description'];
    }

    protected function sortableColumns(): array
    {
        return ['title', 'sort_order', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        // Videos hang off products, so a company-scoped admin filters through
        // the product rather than on a column of its own.
        if (! empty($filters['company_id'])) {
            $query->whereHas('product', fn (Builder $q) => $q->where('company_id', $filters['company_id']));
        }
    }
}
