<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone'));
        $normalizedPhone = ltrim($phone, '0');

        $this->merge([
            'phone' => $normalizedPhone,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'surname' => 'required|string',
            'phone' => 'required|regex:/^5\d{9}$/|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute alanı zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'phone.regex' => 'Telefon numarası 5 ile başlayan 10 haneli olmalıdır.',
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