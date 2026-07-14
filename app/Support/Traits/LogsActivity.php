<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Services\Audit\ActivityLogger;

/**
 * Automatically records created / updated / deleted / restored events for a
 * model to the activity_logs table, including a before/after diff.
 *
 * Sensitive attributes are stripped from the diff via $activityExcluded (which
 * defaults to the model's $hidden list plus timestamps).
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model): void {
            $model->recordActivity('created', [], $model->activityAttributes($model->getAttributes()));
        });

        static::updated(function ($model): void {
            $changed = $model->activityAttributes($model->getChanges());

            if ($changed === []) {
                return; // Nothing meaningful changed (e.g. only excluded columns).
            }

            $old = [];
            foreach (array_keys($changed) as $key) {
                $old[$key] = $model->getOriginal($key);
            }

            $model->recordActivity('updated', $old, $changed);
        });

        static::deleted(function ($model): void {
            $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? 'force_deleted'
                : 'deleted';

            $model->recordActivity($event, [], []);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model): void {
                $model->recordActivity('restored', [], []);
            });
        }
    }

    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $attributes
     */
    public function recordActivity(string $event, array $old, array $attributes): void
    {
        app(ActivityLogger::class)->logModelChange($event, $this, $old, $attributes);
    }

    /**
     * Remove sensitive / noisy attributes before persisting a diff.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function activityAttributes(array $attributes): array
    {
        return array_diff_key($attributes, array_flip($this->activityExcluded()));
    }

    /**
     * @return list<string>
     */
    public function activityExcluded(): array
    {
        return array_values(array_unique(array_merge(
            $this->hidden ?? [],
            ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'],
            property_exists($this, 'activityExclude') ? $this->activityExclude : [],
        )));
    }
}
