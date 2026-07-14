<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic Eloquent repository. Concrete repositories extend this and provide a
 * model() and (optionally) override applyFilters() / searchableColumns().
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    /** @return class-string<Model> */
    abstract protected function model(): string;

    /**
     * Columns scanned by the generic `search` filter.
     *
     * @return list<string>
     */
    protected function searchableColumns(): array
    {
        return [];
    }

    /**
     * Sort columns clients are allowed to order by (whitelist to avoid injection).
     *
     * @return list<string>
     */
    protected function sortableColumns(): array
    {
        return ['created_at', 'updated_at', 'id'];
    }

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function findByUuid(string $uuid): ?Model
    {
        return $this->query()->where('uuid', $uuid)->first();
    }

    public function findByUuidOrFail(string $uuid): Model
    {
        return $this->query()->where('uuid', $uuid)->firstOrFail();
    }

    public function create(array $attributes): Model
    {
        $model = $this->model->newInstance();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function paginate(
        array $filters = [],
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        array $with = [],
    ): LengthAwarePaginator {
        $query = $this->query()->with($with);

        $this->applyFilters($query, $filters);

        $sortBy = in_array($sortBy, $this->sortableColumns(), true) ? $sortBy : 'created_at';
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();
    }

    /**
     * @param array<string, mixed> $filters
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '' && $this->searchableColumns() !== []) {
            $query->where(function (Builder $q) use ($search): void {
                foreach ($this->searchableColumns() as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    protected function makeModel(): Model
    {
        $class = $this->model();

        return new $class;
    }
}
