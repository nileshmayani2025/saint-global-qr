<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Black/white list entry used by the fraud engine to block or trust a device,
 * IP, user or QR code.
 *
 * @property string $list_type
 * @property string $entry_type
 * @property string $value
 */
class AccessList extends Model
{
    use AuditableModel;

    protected $table = 'access_lists';

    public const LIST_BLACKLIST = 'blacklist';
    public const LIST_WHITELIST = 'whitelist';

    public const ENTRY_DEVICE = 'device';
    public const ENTRY_IP = 'ip';
    public const ENTRY_USER = 'user';
    public const ENTRY_CODE = 'code';

    protected $fillable = [
        'company_id',
        'list_type',
        'entry_type',
        'value',
        'reason',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeBlacklist(Builder $query): Builder
    {
        return $query->where('list_type', self::LIST_BLACKLIST);
    }
}
