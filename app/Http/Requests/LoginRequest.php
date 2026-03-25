<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $login = trim((string) $this->input('login'));

        if (preg_match('/^0?5\d{9}$/', $login)) {
            $login = ltrim($login, '0');
        }

        $this->merge([
            'login' => $login,
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => 'required',
            'password' => 'required',
        ];
    }
}