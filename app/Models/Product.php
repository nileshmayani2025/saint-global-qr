<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\AuditableModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property string $name
 * @property string $sku
 * @property float $mrp
 * @property int $reward_points
 * @property string $status
 */
class Product extends Model
{
    use AuditableModel;

    protected $fillable = [
        'company_id',
        'brand_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'hsn_code',
        'description',
        'unit',
        'mrp',
        'reward_points',
        'image_path',
        'status',
        'meta',
    ];

    protected $casts = [
        'mrp' => 'decimal:2',
        'reward_points' => 'integer',
        'meta' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }
}
