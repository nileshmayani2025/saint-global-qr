<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Authorises product actions against the "products.*" permission set. The
 * super-admin bypass is handled globally by Gate::before, so these checks only
 * gate ordinary roles. Company scoping ensures users act within their company.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view') && $this->sameCompany($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->can('products.create');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update') && $this->sameCompany($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.delete') && $this->sameCompany($user, $product);
    }

    private function sameCompany(User $user, Product $product): bool
    {
        return $user->company_id === null || $user->company_id === $product->company_id;
    }
}
