<?php

declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Concerns\ValidatesLocation;
use App\Models\Lead;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadRequest extends FormRequest
{
    use ValidatesLocation;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $companyId = $this->user()?->company_id;

        if ($companyId !== null) {
            $this->merge(['company_id' => $companyId]);
        }

        if ($this->filled('phone')) {
            $this->merge(['phone' => Phone::normalize($this->input('phone'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // country_id / state_id / city_id / address, with the state-belongs-to
            // -country and city-belongs-to-state checks.
            ...$this->locationRules(),

            'name' => ['required', 'string', 'max:255'],
            // Same shape as a user's login number, but deliberately NOT unique:
            // the same person can legitimately enquire more than once.
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'remark' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(Lead::statuses())],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->locationMessages(),
            'phone.regex' => __('Enter a valid 10-digit mobile number.'),
        ];
    }
}
