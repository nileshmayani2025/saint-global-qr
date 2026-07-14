<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('brands.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('brands.view') && $this->sameCompany($user, $brand);
    }

    public function create(User $user): bool
    {
        return $user->can('brands.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('brands.update') && $this->sameCompany($user, $brand);
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('brands.delete') && $this->sameCompany($user, $brand);
    }

    private function sameCompany(User $user, Brand $brand): bool
    {
        return $user->company_id === null || $user->company_id === $brand->company_id;
    }
}
