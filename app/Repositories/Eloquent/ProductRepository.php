<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected function model(): string
    {
        return Product::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'slug', 'sku', 'hsn_code', 'description'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'sku', 'mrp', 'reward_points', 'created_at', 'updated_at', 'status', 'id'];
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        foreach (['company_id', 'brand_id', 'category_id'] as $key) {
            if (! empty($filters[$key])) {
                $query->where($key, $filters[$key]);
            }
        }
    }
}
