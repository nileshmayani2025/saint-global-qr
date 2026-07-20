<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Phone;
use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A shareable digital business card for a registered user.
 *
 * Identity fields (name, mobile, city) are read from the owning User rather
 * than copied, so a card never drifts out of date with the profile behind it.
 *
 * @property int $id
 * @property int $user_id
 * @property string $slug
 * @property string|null $business_name
 * @property string $status
 */
class BusinessCard extends Model
{
    use AuditableModel;

    /** Long enough that the public URL cannot be guessed or enumerated. */
    private const SLUG_LENGTH = 20;

    /** @see User::casts() for why foreign keys are cast explicitly. */
    protected $casts = [
        'user_id' => 'integer',
    ];

    protected $fillable = [
        'user_id',
        'slug',
        'business_name',
        'tagline',
        'whatsapp',
        'email',
        'photo_path',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function newSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(self::SLUG_LENGTH));
        } while (self::query()->where('slug', $slug)->exists());

        return $slug;
    }

    public function publicUrl(): string
    {
        return route('card.show', $this->slug);
    }

    /**
     * The number to use for WhatsApp — the card's own if given, otherwise the
     * account's login mobile.
     */
    public function whatsappNumber(): string
    {
        return Phone::normalize($this->whatsapp ?: $this->user?->phone);
    }

    public function contactEmail(): ?string
    {
        return $this->email ?: $this->user?->email;
    }

    public function photoUrl(): ?string
    {
        $path = $this->photo_path ?: $this->user?->avatar_path;

        return $path ? asset('media/'.$path) : null;
    }

    /**
     * vCard 3.0 payload for the "Save to contacts" download.
     *
     * 3.0 rather than 4.0 because Android's contact importer still handles it
     * more reliably, and CRLF line endings are required by the spec.
     */
    public function toVCard(): string
    {
        $user = $this->user;
        $phone = Phone::normalize($user?->phone);
        $whatsapp = $this->whatsappNumber();

        $address = collect([
            $user?->address,
            $user?->city?->name,
            $user?->state?->name,
            $user?->country?->name,
        ])->filter()->implode(', ');

        $lines = ['BEGIN:VCARD', 'VERSION:3.0'];
        $lines[] = 'FN:'.$this->escape((string) $user?->name);
        $lines[] = 'N:'.$this->escape((string) $user?->name).';;;;';

        if ($this->business_name) {
            $lines[] = 'ORG:'.$this->escape($this->business_name);
        }

        if ($this->tagline) {
            $lines[] = 'TITLE:'.$this->escape($this->tagline);
        }

        if ($phone !== '') {
            $lines[] = 'TEL;TYPE=CELL:+91'.$phone;
        }

        if ($whatsapp !== '' && $whatsapp !== $phone) {
            $lines[] = 'TEL;TYPE=CELL:+91'.$whatsapp;
        }

        if ($email = $this->contactEmail()) {
            $lines[] = 'EMAIL;TYPE=INTERNET:'.$this->escape($email);
        }

        if ($address !== '') {
            $lines[] = 'ADR;TYPE=WORK:;;'.$this->escape($address).';;;;';
        }

        $lines[] = 'URL:'.$this->publicUrl();
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * Commas, semicolons and newlines are structural in vCard and must be
     * escaped or the record splits into the wrong fields.
     */
    private function escape(string $value): string
    {
        return str_replace(["\\", "\n", ',', ';'], ['\\\\', '\\n', '\\,', '\\;'], $value);
    }
}
