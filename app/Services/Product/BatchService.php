<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Models\Batch;
use App\Models\Product;
use App\Repositories\Contracts\BatchRepositoryInterface;
use App\Services\CrudService;
use Illuminate\Database\Eloquent\Model;

class BatchService extends CrudService
{
    public function __construct(BatchRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Derive company_id from the parent product so batches always stay
     * consistent with their product's ownership.
     */
    protected function prepareForCreate(array $data): array
    {
        $data['company_id'] = $this->resolveCompanyId((int) $data['product_id']);
        $data['status'] ??= Batch::STATUS_DRAFT;

        return $data;
    }

    protected function prepareForUpdate(array $data, Model $model): array
    {
        if (! empty($data['product_id'])) {
            $data['company_id'] = $this->resolveCompanyId((int) $data['product_id']);
        }

        return $data;
    }

    private function resolveCompanyId(int $productId): int
    {
        return (int) Product::query()->whereKey($productId)->value('company_id');
    }
}
