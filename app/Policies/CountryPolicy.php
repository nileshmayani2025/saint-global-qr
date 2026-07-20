<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Country;
use App\Models\User;

/**
 * Countries are global reference data, so unlike the catalogue policies there
 * is no company ownership check — only the permission matters.
 */
class CountryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('countries.view');
    }

    public function view(User $user, Country $country): bool
    {
        return $user->can('countries.view');
    }

    public function create(User $user): bool
    {
        return $user->can('countries.create');
    }

    public function update(User $user, Country $country): bool
    {
        return $user->can('countries.update');
    }

    public function delete(User $user, Country $country): bool
    {
        return $user->can('countries.delete');
    }
}
