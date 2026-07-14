<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $uuid
 * @property int $company_id
 * @property int $batch_id
 * @property int $product_id
 * @property string $code
 * @property int $serial
 * @property string $payload_hash
 * @property int $reward_points
 * @property string $status
 * @property int $scan_count
 */
class QrCode extends Model
{
    use AuditableModel;

    public const STATUS_GENERATED = 'generated';
    public const STATUS_PRINTED = 'printed';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'company_id',
        'batch_id',
        'product_id',
        'code',
        'serial',
        'payload_hash',
        'image_path',
        'short_url',
        'reward_points',
        'status',
        'scan_count',
        'first_scanned_at',
        'verified_at',
        'activated_at',
    ];

    protected $casts = [
        'serial' => 'integer',
        'reward_points' => 'integer',
        'scan_count' => 'integer',
        'first_scanned_at' => 'datetime',
        'verified_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(VerificationLog::class);
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
