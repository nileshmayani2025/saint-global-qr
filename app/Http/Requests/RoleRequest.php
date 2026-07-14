<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        // Normalise the display name into a stable role key on create.
        if (! $this->route('role') && $this->filled('name')) {
            $this->merge(['name' => \Illuminate\Support\Str::of($this->input('name'))->lower()->slug()->value()]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;
        $editing = $roleId !== null;

        return [
            // On edit the name is locked (roles are referenced by key), so only validate on create.
            'name' => $editing
                ? ['nullable']
                : ['required', 'string', 'max:60', 'regex:/^[a-z0-9\-]+$/', Rule::unique('roles', 'name')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'The role key may only contain lowercase letters, numbers and hyphens.',
        ];
    }
}
