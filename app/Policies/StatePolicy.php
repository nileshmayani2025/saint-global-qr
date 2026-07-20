<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\State;
use App\Models\User;

class StatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('states.view');
    }

    public function view(User $user, State $state): bool
    {
        return $user->can('states.view');
    }

    public function create(User $user): bool
    {
        return $user->can('states.create');
    }

    public function update(User $user, State $state): bool
    {
        return $user->can('states.update');
    }

    public function delete(User $user, State $state): bool
    {
        return $user->can('states.delete');
    }
}
