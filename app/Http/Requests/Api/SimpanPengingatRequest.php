<?php

namespace App\Http\Requests\Api;

class SimpanPengingatRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:80'],
            'kategori' => ['required', 'string', 'max:50'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'aturan' => ['required', 'in:bulanan,mingguan,tahunan'],
            'jatuh_tempo' => ['required', 'date_format:Y-m-d'],
            'metode' => ['nullable', 'string', 'max:30'],
            'kantong_tujuan' => ['nullable', 'string', 'max:30'],
            'pencatat' => ['required', 'string', 'max:50'],
            'aktif' => ['sometimes', 'boolean'],
            'client_key' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama tagihan wajib diisi.',
            'nama.max' => 'Nama tagihan maksimal 80 karakter.',
            'kategori.required' => 'Kategori wajib diisi.',
            'kategori.max' => 'Kategori maksimal 50 karakter.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.integer' => 'Jumlah harus bilangan bulat.',
            'jumlah.min' => 'Jumlah harus lebih dari nol.',
            'aturan.required' => 'Aturan ulang wajib diisi.',
            'aturan.in' => 'Aturan ulang tidak valid.',
            'jatuh_tempo.required' => 'Jatuh tempo wajib diisi.',
            'jatuh_tempo.date_format' => 'Jatuh tempo harus berformat YYYY-MM-DD.',
            'pencatat.required' => 'Pencatat wajib diisi.',
            'pencatat.max' => 'Pencatat maksimal 50 karakter.',
            'client_key.max' => 'client_key maksimal 64 karakter.',
        ];
    }
}
