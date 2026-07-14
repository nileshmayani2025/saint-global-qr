<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\Access\AccessControl;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $target): bool
    {
        return $user->can('users.view') && $this->sameCompany($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $target): bool
    {
        // Nobody but a super-admin may edit a super-admin.
        if ($target->hasRole(AccessControl::ROLE_SUPER_ADMIN) && ! $user->hasRole(AccessControl::ROLE_SUPER_ADMIN)) {
            return false;
        }

        return $user->can('users.update') && $this->sameCompany($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false; // Cannot delete yourself.
        }

        if ($target->hasRole(AccessControl::ROLE_SUPER_ADMIN)) {
            return false; // Never delete a super-admin through the UI.
        }

        return $user->can('users.delete') && $this->sameCompany($user, $target);
    }

    private function sameCompany(User $user, User $target): bool
    {
        return $user->company_id === null || $user->company_id === $target->company_id;
    }
}
