<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('wallets.view');
    }

    public function view(User $user, Wallet $wallet): bool
    {
        // Owners can always see their own wallet; staff need the permission + company match.
        // Compared as integers — foreign keys come back as strings from a PDO
        // with emulated prepares, which would lock an owner out of their own row.
        if ((int) $wallet->user_id === (int) $user->id) {
            return true;
        }

        return $user->can('wallets.view') && $this->sameCompany($user, $wallet);
    }

    public function adjust(User $user, Wallet $wallet): bool
    {
        return ($user->can('wallets.credit') || $user->can('wallets.debit'))
            && $this->sameCompany($user, $wallet);
    }

    private function sameCompany(User $user, Wallet $wallet): bool
    {
        return $user->company_id === null || $user->company_id === $wallet->company_id;
    }
}
