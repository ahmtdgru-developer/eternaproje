<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'phone' => $this->normalizePhone($data['phone']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_USER,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $data)
    {
        $login = $data['login'];
        $phoneLogin = $this->normalizePhone($login);

        $user = User::where('email', $login)
            ->orWhere('phone', $phoneLogin)
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new \Exception('Giriş bilgileri hatalı.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout($user)
    {
        $user->tokens()->delete();
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);

        return ltrim($digits, '0');
    }
}