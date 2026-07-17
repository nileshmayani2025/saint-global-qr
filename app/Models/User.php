<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\Blameable;
use App\Support\Traits\HasUuid;
use App\Support\Traits\LogsActivity;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string $status
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Blameable;
    use HasFactory;
    use HasRoles;
    use HasUuid;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'company_id',
        'avatar_path',
        'status',
        'approved_at',
        'approved_by',
        'two_factor_enabled',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Kept as an audit record of when the account was granted access. Nothing
     * gates on it — every account can scan from the moment it is created.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function wallets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function rewardWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Wallet::class)->where('type', Wallet::TYPE_REWARD);
    }

    /**
     * Consumers (karigar / contractor / retailer …) hold no management
     * permissions — they scan to earn points. They get the mobile app shell
     * and the scan-to-earn home instead of the admin dashboard.
     */
    public function isConsumer(): bool
    {
        return ! $this->canAny(['products.view', 'batches.view', 'qr-codes.view', 'users.view', 'wallets.credit']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
