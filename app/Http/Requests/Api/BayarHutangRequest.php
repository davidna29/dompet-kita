<?php

namespace App\Http\Requests\Api;

class BayarHutangRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'metode' => ['nullable', 'string', 'max:30'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'pencatat' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.required' => 'Jumlah bayar wajib diisi.',
            'jumlah.integer' => 'Jumlah bayar harus bilangan bulat.',
            'jumlah.min' => 'Jumlah bayar harus lebih dari nol.',
            'tanggal.date_format' => 'Tanggal harus berformat YYYY-MM-DD.',
            'pencatat.required' => 'Pencatat wajib diisi.',
            'pencatat.max' => 'Pencatat maksimal 50 karakter.',
        ];
    }
}
