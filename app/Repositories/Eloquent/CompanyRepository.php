<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    protected function model(): string
    {
        return Company::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'legal_name', 'slug', 'email', 'phone', 'gstin', 'city', 'state'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'created_at', 'updated_at', 'status', 'id'];
    }
}
