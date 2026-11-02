<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $key = $this->throttleKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            event(new Lockout($this));
            $detik = RateLimiter::availableIn($key);

            $exception = ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ]);
            $exception->status = 429;

            throw $exception;
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            $tersisa = max(0, 5 - RateLimiter::attempts($key));
            $pesan = $tersisa > 0
                ? 'Email atau kata sandi tidak cocok.'
                : 'Batas percobaan tercapai. Coba lagi dalam 60 detik.';

            throw ValidationException::withMessages(['email' => $pesan]);
        }

        RateLimiter::clear($key);
    }

    private function throttleKey(): string
    {
        return 'login:'.Str::lower((string) $this->ip());
    }
}
