<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A composed push campaign. One row per "send", with a recipient row per user
 * so the in-app inbox survives a missed or dismissed browser notification.
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $audience
 * @property array|null $audience_filters
 * @property string $status
 */
class PushNotification extends Model
{
    use AuditableModel;

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_ROLE = 'role';
    public const AUDIENCE_USERS = 'users';
    public const AUDIENCE_LOCATION = 'location';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'title',
        'body',
        'image_path',
        'action_url',
        'audience',
        'audience_filters',
        'status',
        'recipient_count',
        'sent_count',
        'failed_count',
        'failure_reason',
        'sent_at',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'recipient_count' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(PushNotificationRecipient::class);
    }

    /**
     * @return list<string>
     */
    public static function audiences(): array
    {
        return [self::AUDIENCE_ALL, self::AUDIENCE_ROLE, self::AUDIENCE_USERS, self::AUDIENCE_LOCATION];
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Only drafts and outright failures can be re-sent — anything queued or in
     * flight would double-deliver.
     */
    public function isSendable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_FAILED], true);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }
}
