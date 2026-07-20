<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\BusinessCard;
use App\Repositories\Contracts\BusinessCardRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BusinessCardRepository extends BaseRepository implements BusinessCardRepositoryInterface
{
    protected function model(): string
    {
        return BusinessCard::class;
    }

    protected function sortableColumns(): array
    {
        return ['business_name', 'status', 'created_at', 'updated_at', 'id'];
    }

    /**
     * Search is handled here rather than through searchableColumns() because it
     * has to reach the owning user's name and mobile as well as the card's own
     * columns — and the whole OR group must stay bracketed so it cannot leak
     * past the company filter below.
     */
    protected function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('tagline', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('user', function (Builder $u) use ($search): void {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['company_id'])) {
            $query->whereHas('user', fn (Builder $q) => $q->where('company_id', $filters['company_id']));
        }
    }
}
