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
 * @property int $user_id
 * @property string $type
 * @property float $balance
 * @property string $status
 */
class Wallet extends Model
{
    use AuditableModel;

    public const TYPE_REWARD = 'reward';
    public const TYPE_CASHBACK = 'cashback';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_FROZEN = 'frozen';

    protected $fillable = [
        'user_id',
        'company_id',
        'type',
        'balance',
        'lifetime_credited',
        'lifetime_debited',
        'currency',
        'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'lifetime_credited' => 'decimal:2',
        'lifetime_debited' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}
