<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransaksiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal->format('Y-m-d'),
            'jenis' => $this->jenis,
            'kategori' => $this->kategori,
            'jumlah' => (int) $this->jumlah,
            'keterangan' => $this->keterangan,
            'pencatat' => $this->pencatat,
            'metode' => $this->metode,
            'sumber' => $this->sumber,
            'client_key' => $this->client_key,
            'kantong_tujuan' => $this->kantong_tujuan,
            'created_at' => waktu_api($this->created_at),
            'updated_at' => waktu_api($this->updated_at),
            'deleted_at' => $this->deleted_at ? waktu_api($this->deleted_at) : null,
        ];
    }
}
