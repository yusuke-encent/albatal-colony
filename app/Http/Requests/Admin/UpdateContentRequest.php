<?php

namespace App\Http\Requests\Admin;

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
            'price' => ['required', 'integer', 'min:100'],
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
            'genre_id.required' => 'Please select a genre.',
            'title.required' => 'Please enter a title.',
            'description.required' => 'Please enter a content description.',
            'price.required' => 'Please enter a selling price.',
            'preview_images.max' => 'You may upload up to 3 preview images.',
        ];
    }
}
