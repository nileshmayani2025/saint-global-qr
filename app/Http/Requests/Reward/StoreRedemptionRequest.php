<?php

declare(strict_types=1);

namespace App\Http\Requests\Reward;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedemptionRequest extends FormRequest
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
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999.99'],
            'method' => ['required', Rule::in(['upi', 'bank', 'gift', 'cash'])],
            'upi_id' => ['nullable', 'required_if:method,upi', 'string', 'max:100'],
            'account_name' => ['nullable', 'required_if:method,bank', 'string', 'max:120'],
            'account_number' => ['nullable', 'required_if:method,bank', 'string', 'max:30'],
            'ifsc' => ['nullable', 'required_if:method,bank', 'string', 'max:20'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payoutDetails(): array
    {
        return match ($this->input('method')) {
            'upi' => ['upi_id' => $this->input('upi_id')],
            'bank' => [
                'account_name' => $this->input('account_name'),
                'account_number' => $this->input('account_number'),
                'ifsc' => $this->input('ifsc'),
            ],
            default => [],
        };
    }
}
