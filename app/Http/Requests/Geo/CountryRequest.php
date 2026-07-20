<?php

declare(strict_types=1);

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
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
        $countryId = $this->route('country')?->id;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('countries', 'name')->ignore($countryId)->whereNull('deleted_at'),
            ],
            'iso2' => [
                'nullable', 'string', 'size:2', 'alpha',
                Rule::unique('countries', 'iso2')->ignore($countryId)->whereNull('deleted_at'),
            ],
            'iso3' => [
                'nullable', 'string', 'size:3', 'alpha',
                Rule::unique('countries', 'iso3')->ignore($countryId)->whereNull('deleted_at'),
            ],
            'phone_code' => ['nullable', 'string', 'max:8'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
