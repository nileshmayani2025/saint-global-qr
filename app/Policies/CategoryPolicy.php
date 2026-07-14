<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('categories.view') && $this->sameCompany($user, $category);
    }

    public function create(User $user): bool
    {
        return $user->can('categories.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('categories.update') && $this->sameCompany($user, $category);
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('categories.delete') && $this->sameCompany($user, $category);
    }

    private function sameCompany(User $user, Category $category): bool
    {
        return $user->company_id === null || $user->company_id === $category->company_id;
    }
}
