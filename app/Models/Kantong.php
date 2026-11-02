<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kantong extends Model
{
    use SoftDeletes;

    protected $table = 'kantong';

    protected $fillable = [
        'nama',
        'saldo_awal',
        'tanggal_saldo_awal',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'saldo_awal' => 'integer',
            'tanggal_saldo_awal' => 'date',
            'urutan' => 'integer',
        ];
    }

    public function setTanggalSaldoAwalAttribute(mixed $nilai): void
    {
        $this->attributes['tanggal_saldo_awal'] = $nilai instanceof \DateTimeInterface
            ? $nilai->format('Y-m-d')
            : \Carbon\Carbon::parse((string) $nilai)->toDateString();
    }
}
