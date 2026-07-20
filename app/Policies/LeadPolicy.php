<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

/**
 * Leads follow the catalogue pattern — permission plus company ownership.
 *
 * On top of that, "leads.view-all" decides whether a user sees the whole
 * company's pipeline or only the leads they captured themselves. A salesman
 * granted plain leads.view therefore keeps to their own list.
 */
class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('leads.view')
            && $this->sameCompany($user, $lead)
            && $this->ownsOrSeesAll($user, $lead);
    }

    public function create(User $user): bool
    {
        return $user->can('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('leads.update')
            && $this->sameCompany($user, $lead)
            && $this->ownsOrSeesAll($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('leads.delete')
            && $this->sameCompany($user, $lead)
            && $this->ownsOrSeesAll($user, $lead);
    }

    private function sameCompany(User $user, Lead $lead): bool
    {
        return $user->company_id === null || $user->company_id === $lead->company_id;
    }

    private function ownsOrSeesAll(User $user, Lead $lead): bool
    {
        // (int) on both sides: Eloquent casts a model's own id, but not foreign
        // keys, so on a server whose PDO returns strings a strict comparison of
        // created_by against id is never true — and an owner is locked out of
        // their own lead.
        return $user->can('leads.view-all') || (int) $lead->created_by === (int) $user->id;
    }
}
