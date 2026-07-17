<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;

class BannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('banners.view');
    }

    public function view(User $user, Banner $banner): bool
    {
        return $user->can('banners.view') && $this->sameCompany($user, $banner);
    }

    public function create(User $user): bool
    {
        return $user->can('banners.create');
    }

    public function update(User $user, Banner $banner): bool
    {
        return $user->can('banners.update') && $this->sameCompany($user, $banner);
    }

    public function delete(User $user, Banner $banner): bool
    {
        return $user->can('banners.delete') && $this->sameCompany($user, $banner);
    }

    private function sameCompany(User $user, Banner $banner): bool
    {
        return $user->company_id === null || $user->company_id === $banner->company_id;
    }
}
