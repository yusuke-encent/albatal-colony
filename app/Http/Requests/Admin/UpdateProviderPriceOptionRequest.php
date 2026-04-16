<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderPriceOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $creator = $this->route('creator');
        $creatorId = $creator?->id;
        $priceOption = $this->route('priceOption');
        $priceOptionId = $priceOption?->id;

        return [
            'price' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('provider_price_options', 'price')
                    ->ignore($priceOptionId)
                    ->where(fn ($query) => $query->where('provider_id', $creatorId)),
            ],
            'product_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('provider_price_options', 'product_code')
                    ->ignore($priceOptionId)
                    ->where(fn ($query) => $query->where('provider_id', $creatorId)),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'price.required' => 'Please enter a price.',
            'price.unique' => 'That price already exists for this creator.',
            'product_code.required' => 'Please enter a product code.',
            'product_code.unique' => 'That product code already exists for this creator.',
        ];
    }
}
