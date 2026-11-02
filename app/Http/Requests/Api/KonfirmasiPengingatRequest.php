<?php

namespace App\Http\Requests\Api;

class KonfirmasiPengingatRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'jumlah' => ['sometimes', 'numeric', 'min:1'],
            'tanggal' => ['sometimes', 'date_format:Y-m-d'],
            'jatuh_tempo' => ['sometimes', 'date_format:Y-m-d'],
            'keterangan' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }
}
