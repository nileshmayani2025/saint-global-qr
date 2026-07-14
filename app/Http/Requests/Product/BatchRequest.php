<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest
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
        $companyId = (int) ($this->user()?->company_id ?? 0);
        $batchId = $this->route('batch')?->id;

        return [
            'product_id' => [
                'required', 'integer',
                $companyId > 0
                    ? Rule::exists('products', 'id')->where('company_id', $companyId)
                    : Rule::exists('products', 'id'),
            ],
            'code' => [
                'required', 'string', 'max:100',
                Rule::unique('batches', 'code')
                    ->where(fn ($q) => $q->where('product_id', (int) $this->input('product_id'))->whereNull('deleted_at'))
                    ->ignore($batchId),
            ],
            'manufacture_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:manufacture_date'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reward_points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(['draft', 'generating', 'active', 'closed'])],
        ];
    }
}
