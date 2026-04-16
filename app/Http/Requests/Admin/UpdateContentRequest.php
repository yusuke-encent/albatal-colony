<?php

namespace App\Http\Requests\Admin;

use App\Models\ProviderPriceOption;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentRequest extends FormRequest
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
        return [
            'provider_id' => [
                'required',
                Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('role', UserRole::Provider)),
            ],
            'genre_id' => ['required', 'exists:genres,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'provider_price_option_id' => [
                'required',
                'integer',
                Rule::exists(ProviderPriceOption::class, 'id')->where(
                    fn ($query) => $query->where('provider_id', $this->integer('provider_id')),
                ),
            ],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'preview_images' => ['nullable', 'array', 'max:3'],
            'preview_images.*' => ['image', 'max:5120'],
            'download_file' => ['nullable', 'file', 'max:512000'],
            'tag_names' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'provider_id.required' => 'Please select a content provider.',
            'provider_price_option_id.required' => 'Please select a selling price.',
            'provider_price_option_id.exists' => 'Please select a valid price option for this creator.',
            'genre_id.required' => 'Please select a genre.',
            'title.required' => 'Please enter a title.',
            'description.required' => 'Please enter a content description.',
            'preview_images.max' => 'You may upload up to 3 preview images.',
        ];
    }
}
