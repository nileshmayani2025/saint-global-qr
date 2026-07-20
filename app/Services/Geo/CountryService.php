<?php

declare(strict_types=1);

namespace App\Services\Geo;

use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Services\CrudService;
use Illuminate\Database\Eloquent\Model;

class CountryService extends CrudService
{
    public function __construct(CountryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * ISO codes are stored upper-case so lookups and the unique index behave
     * predictably regardless of how they were typed in.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareForCreate(array $data): array
    {
        return $this->normalise($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareForUpdate(array $data, Model $model): array
    {
        return $this->normalise($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalise(array $data): array
    {
        foreach (['iso2', 'iso3'] as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                $data[$key] = strtoupper((string) $data[$key]);
            } else {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
