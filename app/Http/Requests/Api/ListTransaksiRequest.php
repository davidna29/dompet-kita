<?php

namespace App\Http\Requests\Api;

class ListTransaksiRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'since' => ['nullable', 'date_format:Y-m-d\TH:i:s'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
            'offset' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
