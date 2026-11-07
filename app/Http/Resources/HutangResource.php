<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HutangResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pihak' => $this->pihak,
            'jenis' => $this->jenis,
            'nominal' => (int) $this->nominal,
            'sisa' => (int) $this->sisa,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'jatuh_tempo' => $this->jatuh_tempo?->format('Y-m-d'),
            'status' => $this->status,
            'keterangan' => $this->keterangan,
            'created_at' => waktu_api($this->created_at),
            'updated_at' => waktu_api($this->updated_at),
            'deleted_at' => $this->deleted_at ? waktu_api($this->deleted_at) : null,
        ];
    }
}
