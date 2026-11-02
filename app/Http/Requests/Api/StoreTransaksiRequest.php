<?php

namespace App\Http\Requests\Api;

class StoreTransaksiRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date_format:Y-m-d'],
            'jenis' => ['required', 'in:masuk,keluar'],
            'kategori' => ['required', 'string', 'max:50'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'pencatat' => ['required', 'string', 'max:50'],
            'metode' => ['nullable', 'string', 'max:30'],
            'sumber' => ['sometimes', 'in:telegram,ui'],
            'client_key' => ['nullable', 'string', 'max:64'],
            'kantong_tujuan' => ['nullable', 'string', 'max:30'],
        ];
    }
}
