<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract every Eloquent-backed repository fulfils. Keeps controllers and
 * services free of query-builder details and makes the data layer swappable.
 */
interface RepositoryInterface
{
    /** @return Collection<int, Model> */
    public function all(array $columns = ['*']): Collection;

    public function find(int|string $id): ?Model;

    public function findOrFail(int|string $id): Model;

    public function findByUuid(string $uuid): ?Model;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Model;

    /** @param array<string, mixed> $attributes */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;

    /**
     * Paginated, filtered, sorted listing for index screens & reports.
     *
     * @param array<string, mixed> $filters
     */
    public function paginate(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $with = [],
    ): LengthAwarePaginator;
}
