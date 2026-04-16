<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateCreatorRequest extends FormRequest
{
    use ProfileValidationRules;

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

        return [
            ...$this->profileRules($creatorId),
            'apc_merchant_id' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('users', 'apc_merchant_id')->ignore($creatorId),
            ],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'new_price_option' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('provider_price_options', 'price')->where(
                    fn ($query) => $query->where('provider_id', $creatorId),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a creator name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'That email address is already in use.',
            'apc_merchant_id.required' => 'Please enter an APC merchant ID.',
            'apc_merchant_id.unique' => 'That APC merchant ID is already in use.',
            'password.confirmed' => 'Password confirmation does not match.',
            'new_price_option.integer' => 'Please enter a valid numeric price.',
            'new_price_option.unique' => 'That price already exists for this creator.',
        ];
    }
}
