<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\CrudService;
use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoryService extends CrudService
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function prepareForCreate(array $data): array
    {
        $companyId = (int) $data['company_id'];

        $data['slug'] = SlugGenerator::make(
            Category::class,
            $data['slug'] ?? $data['name'],
            scope: fn (Builder $q): Builder => $q->where('company_id', $companyId),
        );

        return $data;
    }

    protected function prepareForUpdate(array $data, Model $model): array
    {
        $companyId = (int) ($data['company_id'] ?? $model->company_id);

        if (! empty($data['name']) || ! empty($data['slug'])) {
            $data['slug'] = SlugGenerator::make(
                Category::class,
                $data['slug'] ?? $data['name'] ?? $model->name,
                scope: fn (Builder $q): Builder => $q->where('company_id', $companyId),
                ignoreId: $model->id,
            );
        }

        return $data;
    }
}
