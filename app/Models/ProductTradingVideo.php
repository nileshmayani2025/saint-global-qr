<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A trading video attached to a product. Shown in the consumer app as a
 * welcome popup and listed per product.
 *
 * @property int $id
 * @property int $product_id
 * @property string|null $title
 * @property string $url
 * @property string $status
 */
class ProductTradingVideo extends Model
{
    use AuditableModel;

    protected $fillable = [
        'product_id',
        'title',
        'url',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Falls back to the product name so a video always has something to show.
     */
    public function displayTitle(): string
    {
        return $this->title ?: ($this->product?->name ?? 'Product video');
    }

    /**
     * An <iframe>-safe URL, or null when the link is not a recognised host and
     * should be offered as a plain link instead.
     *
     * Handles the YouTube forms people actually paste: watch?v=, youtu.be/,
     * /shorts/ and /embed/.
     */
    public function embedUrl(): ?string
    {
        $url = trim($this->url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[1].'?rel=0&modestbranding=1';
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    /**
     * True when the URL points straight at a video file we can hand to <video>.
     */
    public function isDirectFile(): bool
    {
        return (bool) preg_match('~\.(mp4|webm|ogg|mov)(\?.*)?$~i', $this->url);
    }
}
