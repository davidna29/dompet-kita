<?php

namespace App\Http\Requests\Api;

class SimpanAnggaranRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'periode' => ['required', 'date_format:Y-m'],
            'kategori' => ['present', 'nullable', 'string', 'max:50'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'ambang' => ['sometimes', 'nullable', 'integer', 'between:1,100'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode.required' => 'Periode wajib diisi.',
            'periode.date_format' => 'Periode harus berformat YYYY-MM.',
            'kategori.present' => 'Kategori wajib dikirim; gunakan null untuk total bulanan.',
            'kategori.string' => 'Kategori harus berupa teks.',
            'kategori.max' => 'Kategori maksimal 50 karakter.',
            'jumlah.required' => 'Jumlah anggaran wajib diisi.',
            'jumlah.integer' => 'Jumlah anggaran harus bilangan bulat.',
            'jumlah.min' => 'Jumlah anggaran harus lebih dari nol.',
            'ambang.integer' => 'Ambang harus bilangan bulat.',
            'ambang.between' => 'Ambang harus antara 1 sampai 100.',
        ];
    }
}
