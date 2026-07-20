<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityRequest extends FormRequest
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
        $cityId = $this->route('city')?->id;

        return [
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('cities', 'name')
                    ->where('state_id', (int) $this->input('state_id'))
                    ->ignore($cityId)
                    ->whereNull('deleted_at'),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
