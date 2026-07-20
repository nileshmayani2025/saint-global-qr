<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BusinessCard;
use App\Models\User;

/**
 * Governs the ADMIN module only. A user editing their own card goes through
 * MyBusinessCardController, which is scoped to $request->user() and therefore
 * needs no permission at all.
 */
class BusinessCardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('business-cards.view');
    }

    public function view(User $user, BusinessCard $card): bool
    {
        return $user->can('business-cards.view') && $this->sameCompany($user, $card);
    }

    public function update(User $user, BusinessCard $card): bool
    {
        return $user->can('business-cards.update') && $this->sameCompany($user, $card);
    }

    public function delete(User $user, BusinessCard $card): bool
    {
        return $user->can('business-cards.delete') && $this->sameCompany($user, $card);
    }

    /**
     * Ownership follows the card holder's company.
     */
    private function sameCompany(User $user, BusinessCard $card): bool
    {
        return $user->company_id === null || $user->company_id === $card->user?->company_id;
    }
}
