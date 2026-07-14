<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Services\CrudService;
use App\Support\SlugGenerator;
use Illuminate\Database\Eloquent\Model;

class CompanyService extends CrudService
{
    public function __construct(CompanyRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function prepareForCreate(array $data): array
    {
        $data['slug'] = SlugGenerator::make(Company::class, $data['slug'] ?? $data['name']);

        return $data;
    }

    protected function prepareForUpdate(array $data, Model $model): array
    {
        if (! empty($data['name']) || ! empty($data['slug'])) {
            $data['slug'] = SlugGenerator::make(
                Company::class,
                $data['slug'] ?? $data['name'] ?? $model->name,
                ignoreId: $model->id,
            );
        }

        return $data;
    }
}
