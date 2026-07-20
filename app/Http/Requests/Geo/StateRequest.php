<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $stateId = $this->route('state')?->id;

        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            // Names only have to be unique inside their country — "Georgia"
            // can legitimately exist under more than one.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('states', 'name')
                    ->where('country_id', (int) $this->input('country_id'))
                    ->ignore($stateId)
                    ->whereNull('deleted_at'),
            ],
            'code' => ['nullable', 'string', 'max:10'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
