<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $image_path
 * @property string|null $button_label
 * @property string|null $link_url
 * @property int $sort_order
 * @property string $status
 */
class Banner extends Model
{
    use AuditableModel;

    protected $fillable = [
        'company_id',
        'title',
        'subtitle',
        'image_path',
        'button_label',
        'link_url',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Carousel order: lowest sort_order first, newest first as the tie-break.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }
}
