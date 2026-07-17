<?php

declare(strict_types=1);

namespace App\Services\UserManagement;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Handles user lifecycle: creation, updates and role sync. All writes are
 * transactional so a user and their roles commit together.
 */
class UserService
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return User::query()
            ->with('roles:id,name')
            ->when(! empty($filters['company_id']), fn (Builder $q) => $q->where('company_id', $filters['company_id']))
            ->when($search !== '', fn (Builder $q) => $q->where(function (Builder $s) use ($search): void {
                $s->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['role']), fn (Builder $q) => $q->whereHas('roles', fn (Builder $r) => $r->where('name', $filters['role'])))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $roles
     */
    public function create(array $data, array $roles): User
    {
        return DB::transaction(function () use ($data, $roles): User {
            $user = new User;
            $user->fill($this->fillable($data, isCreate: true));

            // Sign-in is by mobile + OTP, so no password is ever used. The
            // column is NOT NULL, so store a value nobody can authenticate with.
            $user->password = Hash::make(Str::random(40));
            $user->save();

            $user->syncRoles($roles);

            return $user;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $roles
     */
    public function update(User $user, array $data, array $roles): User
    {
        return DB::transaction(function () use ($user, $data, $roles): User {
            $user->fill($this->fillable($data, isCreate: false));
            $user->save();
            $user->syncRoles($roles);

            return $user->refresh();
        });
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function fillable(array $data, bool $isCreate): array
    {
        return array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'status' => $data['status'] ?? 'active',
            // Access is granted on creation — there is no approval step. Stamped
            // on every create path so the column never reads as "pending".
            'approved_at' => $isCreate ? now() : null,
        ], static fn ($v) => $v !== null);
    }
}
