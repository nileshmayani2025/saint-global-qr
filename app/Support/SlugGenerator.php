<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Generates URL-safe, collision-free slugs scoped to an optional query
 * constraint (e.g. unique per company).
 */
final class SlugGenerator
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @param callable(Builder):Builder|null $scope
     */
    public static function make(
        string $model,
        string $value,
        string $column = 'slug',
        ?callable $scope = null,
        ?int $ignoreId = null,
    ): string {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 1;

        while (self::exists($model, $column, $slug, $scope, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @param callable(Builder):Builder|null $scope
     */
    private static function exists(string $model, string $column, string $slug, ?callable $scope, ?int $ignoreId): bool
    {
        /** @var Builder $query */
        $query = $model::query()->withTrashed()->where($column, $slug);

        if ($scope !== null) {
            $query = $scope($query);
        }

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
