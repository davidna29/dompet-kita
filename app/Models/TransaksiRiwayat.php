<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiRiwayat extends Model
{
    public $timestamps = false;

    protected $table = 'transaksi_riwayat';

    protected $fillable = [
        'transaksi_id',
        'aksi',
        'oleh',
        'sumber',
        'sebelum',
        'sesudah',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sebelum' => 'array',
            'sesudah' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id')->withTrashed();
    }
}
