<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    protected function model(): string
    {
        return Country::class;
    }

    protected function searchableColumns(): array
    {
        return ['name', 'iso2', 'iso3', 'phone_code'];
    }

    protected function sortableColumns(): array
    {
        return ['name', 'sort_order', 'created_at', 'updated_at', 'status', 'id'];
    }
}
