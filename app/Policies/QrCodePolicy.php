<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('qr-codes.view');
    }

    public function view(User $user, QrCode $qrCode): bool
    {
        return $user->can('qr-codes.view') && $this->sameCompany($user, $qrCode);
    }

    public function print(User $user, QrCode $qrCode): bool
    {
        return $user->can('qr-codes.print') && $this->sameCompany($user, $qrCode);
    }

    public function block(User $user, QrCode $qrCode): bool
    {
        return $user->can('qr-codes.block') && $this->sameCompany($user, $qrCode);
    }

    private function sameCompany(User $user, QrCode $qrCode): bool
    {
        return $user->company_id === null || $user->company_id === $qrCode->company_id;
    }
}
