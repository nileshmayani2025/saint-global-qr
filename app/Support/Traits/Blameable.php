<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Stamps created_by / updated_by / deleted_by from the authenticated user.
 *
 * Designed to work alongside Laravel's SoftDeletes trait. Tables must expose
 * nullable created_by, updated_by and deleted_by BIGINT UNSIGNED columns
 * (added in bulk via the `auditColumns()` Blueprint macro).
 */
trait Blameable
{
    public static function bootBlameable(): void
    {
        static::creating(function ($model): void {
            $userId = Auth::id();

            if ($userId !== null) {
                $model->created_by ??= $userId;
                $model->updated_by ??= $userId;
            }
        });

        static::updating(function ($model): void {
            $userId = Auth::id();

            if ($userId !== null) {
                $model->updated_by = $userId;
            }
        });

        static::deleting(function ($model): void {
            $userId = Auth::id();

            // Only stamp on a soft delete (a force delete removes the row entirely).
            $isSoftDeleting = ! method_exists($model, 'isForceDeleting') || ! $model->isForceDeleting();

            if ($userId !== null && $isSoftDeleting) {
                // runSoftDelete() only writes deleted_at, so persist deleted_by first.
                $model->deleted_by = $userId;
                $model->saveQuietly();
            }
        });

        static::restoring(function ($model): void {
            $model->deleted_by = null;
        });
    }
}
