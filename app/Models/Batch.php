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
 * @property int $company_id
 * @property int $product_id
 * @property string $code
 * @property int $quantity
 * @property int $qr_generated
 * @property string $status
 */
class Batch extends Model
{
    use AuditableModel;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'company_id',
        'product_id',
        'code',
        'manufacture_date',
        'expiry_date',
        'quantity',
        'qr_generated',
        'reward_points',
        'status',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'integer',
        'qr_generated' => 'integer',
        'reward_points' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function remainingToGenerate(): int
    {
        return max(0, $this->quantity - $this->qr_generated);
    }

    public function effectiveRewardPoints(): int
    {
        return $this->reward_points ?? (int) ($this->product?->reward_points ?? 0);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
