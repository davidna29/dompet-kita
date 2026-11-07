<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HutangPiutang extends Model
{
    use SoftDeletes;

    protected $table = 'hutang_piutang';

    protected $fillable = [
        'pihak',
        'jenis',
        'nominal',
        'sisa',
        'tanggal',
        'jatuh_tempo',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
            'sisa' => 'integer',
            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', '!=', 'lunas');
    }

    public function setTanggalAttribute(mixed $nilai): void
    {
        $this->attributes['tanggal'] = $nilai instanceof \DateTimeInterface
            ? $nilai->format('Y-m-d')
            : Carbon::parse((string) $nilai)->toDateString();
    }

    public function setJatuhTempoAttribute(mixed $nilai): void
    {
        $this->attributes['jatuh_tempo'] = $nilai === null
            ? null
            : ($nilai instanceof \DateTimeInterface
                ? $nilai->format('Y-m-d')
                : Carbon::parse((string) $nilai)->toDateString());
    }

    public function getStatusHitungAttribute(): string
    {
        if ($this->status === 'lunas' || $this->sisa <= 0) {
            return 'lunas';
        }

        if ($this->jatuh_tempo !== null && $this->jatuh_tempo->lt(Carbon::today())) {
            return 'terlambat';
        }

        return $this->status;
    }
}
