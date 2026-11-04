<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengingatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'kategori' => $this->kategori,
            'jumlah' => (int) $this->jumlah,
            'aturan' => $this->aturan,
            'jatuh_tempo' => $this->jatuh_tempo?->format('Y-m-d'),
            'metode' => $this->metode,
            'kantong_tujuan' => $this->kantong_tujuan,
            'pencatat' => $this->pencatat,
            'aktif' => (bool) $this->aktif,
            'status' => $this->status,
            'client_key' => $this->client_key,
            'client_key_konfirmasi' => $this->client_key_konfirmasi,
            'terakhir_dikonfirmasi' => $this->terakhir_dikonfirmasi?->format('Y-m-d'),
            'created_at' => waktu_api($this->created_at),
            'updated_at' => waktu_api($this->updated_at),
            'deleted_at' => $this->deleted_at ? waktu_api($this->deleted_at) : null,
        ];
    }
}
