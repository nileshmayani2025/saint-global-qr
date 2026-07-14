<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\QrCode;
use App\Repositories\Contracts\QrCodeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class QrCodeRepository extends BaseRepository implements QrCodeRepositoryInterface
{
    protected function model(): string
    {
        return QrCode::class;
    }

    protected function searchableColumns(): array
    {
        return ['code', 'serial', 'short_url'];
    }

    protected function sortableColumns(): array
    {
        return ['serial', 'code', 'scan_count', 'created_at', 'status', 'id'];
    }

    public function findByCode(string $code): ?QrCode
    {
        return $this->query()->where('code', $code)->first();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        foreach (['company_id', 'batch_id', 'product_id'] as $key) {
            if (! empty($filters[$key])) {
                $query->where($key, $filters[$key]);
            }
        }
    }
}
