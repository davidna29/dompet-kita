<?php

namespace App\Http\Requests\Api;

class UpdatePengingatRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'nama' => ['sometimes', 'string', 'max:80'],
            'kategori' => ['sometimes', 'string', 'max:50'],
            'jumlah' => ['sometimes', 'integer', 'min:1'],
            'aturan' => ['sometimes', 'in:bulanan,mingguan,tahunan'],
            'jatuh_tempo' => ['sometimes', 'date_format:Y-m-d'],
            'metode' => ['sometimes', 'nullable', 'string', 'max:30'],
            'kantong_tujuan' => ['sometimes', 'nullable', 'string', 'max:30'],
            'pencatat' => ['sometimes', 'string', 'max:50'],
            'aktif' => ['sometimes', 'boolean'],
            'client_key' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.max' => 'Nama tagihan maksimal 80 karakter.',
            'kategori.max' => 'Kategori maksimal 50 karakter.',
            'jumlah.integer' => 'Jumlah harus bilangan bulat.',
            'jumlah.min' => 'Jumlah harus lebih dari nol.',
            'aturan.in' => 'Aturan ulang tidak valid.',
            'jatuh_tempo.date_format' => 'Jatuh tempo harus berformat YYYY-MM-DD.',
            'pencatat.max' => 'Pencatat maksimal 50 karakter.',
            'client_key.prohibited' => 'client_key tidak dapat diubah.',
        ];
    }
}
