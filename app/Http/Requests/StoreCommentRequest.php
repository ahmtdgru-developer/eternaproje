<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Yorum alanı zorunludur.',
            'content.string' => 'Yorum alanı metin olmalıdır.',
            'content.min' => 'Yorum en az 3 karakter olmalıdır.',
            'content.max' => 'Yorum en fazla 2000 karakter olabilir.',
        ];
    }
}