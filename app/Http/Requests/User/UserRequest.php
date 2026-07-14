<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Support\Access\AccessControl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Company-scoped admins can only create users inside their own company.
     */
    protected function prepareForValidation(): void
    {
        $companyId = $this->user()?->company_id;

        if ($companyId !== null) {
            $this->merge(['company_id' => $companyId]);
        }

        // Normalise a single role into the roles array.
        if ($this->filled('role') && ! $this->filled('roles')) {
            $this->merge(['roles' => [$this->input('role')]]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isCreate = $userId === null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
            ],
            'phone' => [
                'nullable', 'string', 'max:20',
                Rule::unique('users', 'phone')->ignore($userId)->whereNull('deleted_at'),
            ],
            'password' => [$isCreate ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in($this->assignableRoles())],
        ];
    }

    /**
     * Roles the current user is allowed to grant. Only a super-admin may grant
     * the super-admin role.
     *
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        $roles = AccessControl::roles();

        if (! $this->user()->hasRole(AccessControl::ROLE_SUPER_ADMIN)) {
            $roles = array_values(array_filter($roles, static fn (string $r): bool => $r !== AccessControl::ROLE_SUPER_ADMIN));
        }

        return $roles;
    }
}
