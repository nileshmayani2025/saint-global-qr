<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

/**
 * Shared rules for the Country / State / City / Address block.
 *
 * All four are optional, but the three ids must form a consistent chain: a
 * state has to belong to the chosen country and a city to the chosen state.
 * Without that check a hand-crafted POST could pair Gujarat with Kerala.
 */
trait ValidatesLocation
{
    /**
     * @return array<string, mixed>
     */
    protected function locationRules(): array
    {
        $countryId = (int) $this->input('country_id');
        $stateId = (int) $this->input('state_id');

        return [
            'country_id' => [
                'nullable', 'integer',
                Rule::exists('countries', 'id')->whereNull('deleted_at'),
            ],
            'state_id' => [
                'nullable', 'integer',
                Rule::exists('states', 'id')
                    ->whereNull('deleted_at')
                    ->when($countryId > 0, fn ($rule) => $rule->where('country_id', $countryId)),
            ],
            'city_id' => [
                'nullable', 'integer',
                Rule::exists('cities', 'id')
                    ->whereNull('deleted_at')
                    ->when($stateId > 0, fn ($rule) => $rule->where('state_id', $stateId)),
            ],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function locationMessages(): array
    {
        return [
            'state_id.exists' => __('The selected state does not belong to the selected country.'),
            'city_id.exists' => __('The selected city does not belong to the selected state.'),
        ];
    }
}
