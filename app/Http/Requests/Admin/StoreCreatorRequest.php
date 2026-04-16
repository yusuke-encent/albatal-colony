<?php

namespace App\Http\Requests\Admin;

use App\Concerns\ProfileValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreCreatorRequest extends FormRequest
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
        return $this->profileRules();
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
        ];
    }
}
