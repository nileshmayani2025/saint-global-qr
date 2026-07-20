<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductTradingVideo;
use App\Models\User;

class ProductTradingVideoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('trading-videos.view');
    }

    public function view(User $user, ProductTradingVideo $video): bool
    {
        return $user->can('trading-videos.view') && $this->sameCompany($user, $video);
    }

    public function create(User $user): bool
    {
        return $user->can('trading-videos.create');
    }

    public function update(User $user, ProductTradingVideo $video): bool
    {
        return $user->can('trading-videos.update') && $this->sameCompany($user, $video);
    }

    public function delete(User $user, ProductTradingVideo $video): bool
    {
        return $user->can('trading-videos.delete') && $this->sameCompany($user, $video);
    }

    /**
     * Ownership follows the parent product's company.
     */
    private function sameCompany(User $user, ProductTradingVideo $video): bool
    {
        return $user->company_id === null || $user->company_id === $video->product?->company_id;
    }
}
