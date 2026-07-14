<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $qr_code_id
 * @property int $company_id
 * @property int $product_id
 * @property int $batch_id
 * @property int|null $user_id
 * @property int $reward_points
 * @property string $status
 */
class VerificationLog extends Model
{
    use AuditableModel;

    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REWARDED = 'rewarded';

    protected $fillable = [
        'qr_code_id',
        'scan_id',
        'company_id',
        'product_id',
        'batch_id',
        'user_id',
        'reward_points',
        'latitude',
        'longitude',
        'status',
        'verified_at',
    ];

    protected $casts = [
        'reward_points' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'verified_at' => 'datetime',
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
