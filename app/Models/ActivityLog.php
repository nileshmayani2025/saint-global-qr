<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Immutable audit record. Rows are written by the ActivityLogger and never
 * updated or deleted through the application, so no soft deletes / blame here.
 *
 * @property string $event
 * @property array<string, mixed>|null $properties
 */
class ActivityLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'log_name',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'causer_id',
        'properties',
        'ip_address',
        'user_agent',
        'browser',
        'device',
        'method',
        'url',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
