<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Immutable ledger entry for a wallet. Each row records the amount, the running
 * balance after the movement, and the source that triggered it.
 *
 * @property string $direction
 * @property float $amount
 * @property float $balance_after
 * @property string $reason
 */
class WalletTransaction extends Model
{
    use AuditableModel;

    public const DIRECTION_CREDIT = 'credit';
    public const DIRECTION_DEBIT = 'debit';

    public const REASON_VERIFICATION_REWARD = 'verification_reward';
    public const REASON_CASHBACK = 'cashback';
    public const REASON_REDEMPTION = 'redemption';
    public const REASON_ADJUSTMENT = 'adjustment';
    public const REASON_REVERSAL = 'reversal';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'company_id',
        'direction',
        'amount',
        'balance_after',
        'reason',
        'source_type',
        'source_id',
        'reference',
        'description',
        'status',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCredit(): bool
    {
        return $this->direction === self::DIRECTION_CREDIT;
    }

    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('direction', self::DIRECTION_CREDIT);
    }
}
