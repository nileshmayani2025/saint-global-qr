<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Support\Str;

/**
 * Assigns a public UUID on create and binds routes by uuid instead of id.
 *
 * Tables using this trait must have a unique `uuid CHAR(36)` column.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model): void {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = (string) Str::uuid();
            }
        });
    }

    public function getUuidColumn(): string
    {
        return 'uuid';
    }

    /**
     * Bind route params by uuid (…/products/{product}) while keeping id internal.
     */
    public function getRouteKeyName(): string
    {
        return $this->getUuidColumn();
    }
}
