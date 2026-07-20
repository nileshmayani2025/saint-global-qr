<?php

declare(strict_types=1);

namespace App\Http\Requests\Lead;

use App\Http\Requests\Concerns\ValidatesLocation;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Lead capture from the app.
 *
 * Narrower than LeadRequest on purpose: status and company are decided by the
 * controller, not the form, so an app user cannot mark their own lead as
 * converted or file it against another company.
 */
class AppLeadRequest extends FormRequest
{
    use ValidatesLocation;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
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
            ...$this->locationRules(),
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'remark' => ['nullable', 'string', 'max:2000'],
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
