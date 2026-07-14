<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $qr_code_id
 * @property string $raw_code
 * @property string $result
 * @property bool $is_fraud_suspected
 * @property array<int, string>|null $fraud_reasons
 */
class Scan extends Model
{
    use AuditableModel;

    public const RESULT_VALID = 'valid';
    public const RESULT_DUPLICATE = 'duplicate';
    public const RESULT_INVALID = 'invalid';
    public const RESULT_BLOCKED = 'blocked';
    public const RESULT_EXPIRED = 'expired';

    protected $fillable = [
        'qr_code_id',
        'company_id',
        'user_id',
        'raw_code',
        'result',
        'latitude',
        'longitude',
        'accuracy',
        'ip_address',
        'user_agent',
        'browser',
        'device',
        'device_id',
        'is_fraud_suspected',
        'fraud_reasons',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'accuracy' => 'decimal:2',
        'is_fraud_suspected' => 'boolean',
        'fraud_reasons' => 'array',
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->where('is_fraud_suspected', true);
    }
}
