<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TradingVideoRequest extends FormRequest
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
            'product_id' => [
                'required', 'integer',
                // A company-scoped admin may only attach a video to their own
                // company's products.
                Rule::exists('products', 'id')->where(function ($query): void {
                    $companyId = $this->user()?->company_id;

                    if ($companyId !== null) {
                        $query->where('company_id', $companyId);
                    }
                }),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:1000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.url' => __('Enter a full video link, including https://'),
            'product_id.exists' => __('Choose a product from your own catalogue.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['product_id' => __('product')];
    }

    /**
     * Products the current user may pick from.
     */
    public function selectableProducts()
    {
        return Product::query()
            ->when($this->user()?->company_id, fn ($q, $id) => $q->where('company_id', $id))
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);
    }
}
