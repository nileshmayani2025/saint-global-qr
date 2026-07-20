<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $state_id
 * @property string $name
 * @property int $sort_order
 * @property string $status
 */
class City extends Model
{
    use AuditableModel;

    protected $fillable = [
        'state_id',
        'name',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'state_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForState(Builder $query, int $stateId): Builder
    {
        return $query->where('state_id', $stateId);
    }
}
