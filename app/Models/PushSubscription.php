<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single browser/device FCM registration token.
 *
 * One user can hold several (phone, laptop, second browser). Tokens rotate, so
 * rows are matched on a hash of the token rather than the token text itself —
 * FCM tokens are far too long for a MySQL unique index.
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $token_hash
 */
class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'token_hash',
        'user_agent',
        'last_used_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
