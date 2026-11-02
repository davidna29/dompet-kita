<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
            'name' => trim((string) $request->input('name')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'password.min' => 'Kata sandi minimal 12 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $data['name'],
            'pencatat' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_admin' => false,
        ]);

        return redirect()
            ->route('pengaturan')
            ->with('sukses', 'Akun anggota baru berhasil dibuat.');
    }
}
