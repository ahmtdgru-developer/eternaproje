<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'surname' => 'required|string',
            'phone' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'phone.unique' => 'Bu telefon numarası zaten kayıtlı.',
            'password.min' => 'Şifre en az :min karakter olmalıdır.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Ad',
            'surname' => 'Soyadı',
            'phone' => 'Telefon numarası',
            'email' => 'E-posta',
            'password' => 'Şifre',
        ];
    }
}
