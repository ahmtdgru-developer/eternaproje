<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $categoryIds = $this->input('category_ids');

        if (is_string($categoryIds)) {
            $decoded = json_decode($categoryIds, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'category_ids' => $decoded,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Başlık zorunludur.',
            'content.required' => 'İçerik zorunludur.',
            'cover_image.image' => 'Kapak görseli geçerli bir resim olmalıdır.',
            'cover_image.mimes' => 'Kapak görseli yalnızca jpg, jpeg, png veya webp olabilir.',
            'cover_image.max' => 'Kapak görseli en fazla 2 MB olabilir.',
            'status.required' => 'Durum zorunludur.',
            'status.in' => 'Durum yalnızca draft veya published olabilir.',
            'category_ids.array' => 'Kategoriler dizi formatında gönderilmelidir.',
            'category_ids.*.exists' => 'Seçilen kategorilerden biri geçersiz.',
        ];
    }
}