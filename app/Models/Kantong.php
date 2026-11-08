<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kantong extends Model
{
    use SoftDeletes;

    protected $table = 'kantong';

    protected $fillable = [
        'nama',
        'tipe',
        'saldo_awal',
        'tanggal_saldo_awal',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => 'string',
            'saldo_awal' => 'integer',
            'tanggal_saldo_awal' => 'date',
            'urutan' => 'integer',
        ];
    }

    public function setTanggalSaldoAwalAttribute(mixed $nilai): void
    {
        $this->attributes['tanggal_saldo_awal'] = $nilai instanceof \DateTimeInterface
            ? $nilai->format('Y-m-d')
            : Carbon::parse((string) $nilai)->toDateString();
    }
}
