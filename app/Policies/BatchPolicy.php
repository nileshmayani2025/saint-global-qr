<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('batches.view');
    }

    public function view(User $user, Batch $batch): bool
    {
        return $user->can('batches.view') && $this->sameCompany($user, $batch);
    }

    public function create(User $user): bool
    {
        return $user->can('batches.create');
    }

    public function update(User $user, Batch $batch): bool
    {
        return $user->can('batches.update') && $this->sameCompany($user, $batch);
    }

    public function delete(User $user, Batch $batch): bool
    {
        return $user->can('batches.delete') && $this->sameCompany($user, $batch);
    }

    public function generateQr(User $user, Batch $batch): bool
    {
        return $user->can('qr-codes.generate') && $this->sameCompany($user, $batch);
    }

    private function sameCompany(User $user, Batch $batch): bool
    {
        return $user->company_id === null || $user->company_id === $batch->company_id;
    }
}
