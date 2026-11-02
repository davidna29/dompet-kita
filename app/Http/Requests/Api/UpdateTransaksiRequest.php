<?php

namespace App\Http\Requests\Api;

class UpdateTransaksiRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tanggal' => ['sometimes', 'date_format:Y-m-d'],
            'jenis' => ['sometimes', 'in:masuk,keluar'],
            'kategori' => ['sometimes', 'string', 'max:50'],
            'jumlah' => ['sometimes', 'integer', 'min:1'],
            'keterangan' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'pencatat' => ['sometimes', 'string', 'max:50'],
            'metode' => ['sometimes', 'nullable', 'string', 'max:30'],
            'sumber' => ['sometimes', 'in:telegram,ui'],
            'client_key' => ['sometimes', 'nullable', 'string', 'max:64'],
            'kantong_tujuan' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }
}
