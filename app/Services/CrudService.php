<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Base transactional CRUD service. Aggregate services extend this and override
 * the prepare hooks to add slugs, scoping and other business rules.
 */
abstract class CrudService
{
    public function __construct(protected RepositoryInterface $repository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $with = [],
    ): LengthAwarePaginator {
        return $this->repository->paginate($filters, $perPage, $sortBy, $sortDir, $with);
    }

    public function findByUuidOrFail(string $uuid): Model
    {
        $model = $this->repository->findByUuid($uuid);

        abort_if($model === null, 404);

        return $model;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Model
    {
        return DB::transaction(fn (): Model => $this->repository->create($this->prepareForCreate($data)));
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Model $model, array $data): Model
    {
        return DB::transaction(fn (): Model => $this->repository->update($model, $this->prepareForUpdate($data, $model)));
    }

    public function delete(Model $model): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($model));
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareForCreate(array $data): array
    {
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function prepareForUpdate(array $data, Model $model): array
    {
        return $data;
    }
}
