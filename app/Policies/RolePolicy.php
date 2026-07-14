<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\Access\AccessControl;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        // The super-admin role is protected — only a super-admin may touch it,
        // and even then it is not editable through the UI.
        if ($role->name === AccessControl::ROLE_SUPER_ADMIN) {
            return false;
        }

        return $user->can('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        // Never delete the built-in system roles from the seeded catalogue.
        if (in_array($role->name, AccessControl::roles(), true)) {
            return false;
        }

        return $user->can('roles.delete');
    }
}
