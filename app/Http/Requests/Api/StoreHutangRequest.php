<?php

namespace App\Http\Requests\Api;

class StoreHutangRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'pihak' => ['required', 'string', 'max:50'],
            'jenis' => ['required', 'in:hutang,piutang'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'jatuh_tempo' => ['nullable', 'date_format:Y-m-d'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pihak.required' => 'Nama pihak wajib diisi.',
            'pihak.max' => 'Nama pihak maksimal 50 karakter.',
            'jenis.required' => 'Jenis wajib diisi.',
            'jenis.in' => 'Jenis harus hutang atau piutang.',
            'nominal.required' => 'Nominal wajib diisi.',
            'nominal.integer' => 'Nominal harus bilangan bulat.',
            'nominal.min' => 'Nominal harus lebih dari nol.',
            'tanggal.date_format' => 'Tanggal harus berformat YYYY-MM-DD.',
            'jatuh_tempo.date_format' => 'Jatuh tempo harus berformat YYYY-MM-DD.',
        ];
    }
}
