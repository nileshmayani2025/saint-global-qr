<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RedemptionRequest;
use App\Models\User;

class RedemptionRequestPolicy
{
    public function viewAny(User $user): bool
    {
        // The admin queue is for reviewers; consumers use their own "My Rewards".
        return $user->can('redemptions.approve') || $user->can('redemptions.reject');
    }

    public function view(User $user, RedemptionRequest $request): bool
    {
        if ($request->user_id === $user->id) {
            return true;
        }

        return $user->can('redemptions.view') && $this->sameCompany($user, $request);
    }

    public function create(User $user): bool
    {
        return $user->can('redemptions.create');
    }

    public function approve(User $user, RedemptionRequest $request): bool
    {
        return $request->isPending() && $user->can('redemptions.approve') && $this->sameCompany($user, $request);
    }

    public function reject(User $user, RedemptionRequest $request): bool
    {
        return $request->isPending() && $user->can('redemptions.reject') && $this->sameCompany($user, $request);
    }

    private function sameCompany(User $user, RedemptionRequest $request): bool
    {
        return $user->company_id === null || $user->company_id === $request->company_id;
    }
}
