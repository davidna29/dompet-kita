<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiBerulang extends Model
{
    use SoftDeletes;

    protected $table = 'transaksi_berulang';

    protected $fillable = [
        'nama',
        'kategori',
        'jumlah',
        'aturan',
        'jatuh_tempo',
        'metode',
        'kantong_tujuan',
        'pencatat',
        'aktif',
        'terakhir_dikonfirmasi',
        'client_key',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'jatuh_tempo' => 'date',
            'terakhir_dikonfirmasi' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function setJatuhTempoAttribute(mixed $nilai): void
    {
        $this->attributes['jatuh_tempo'] = $nilai instanceof \DateTimeInterface
            ? $nilai->format('Y-m-d')
            : Carbon::parse((string) $nilai)->toDateString();
    }

    public function setTerakhirDikonfirmasiAttribute(mixed $nilai): void
    {
        $this->attributes['terakhir_dikonfirmasi'] = $nilai === null
            ? null
            : ($nilai instanceof \DateTimeInterface
                ? $nilai->format('Y-m-d')
                : Carbon::parse((string) $nilai)->toDateString());
    }

    public function getStatusAttribute(): string
    {
        if (! $this->aktif) {
            return 'nonaktif';
        }

        $hariIni = Carbon::today();

        if ($this->jatuh_tempo->lt($hariIni)) {
            return 'terlambat';
        }

        if ($this->jatuh_tempo->isSameDay($hariIni)) {
            return 'hari-ini';
        }

        if ($this->jatuh_tempo->lte($hariIni->copy()->addDays(7))) {
            return 'segera';
        }

        return 'akan-datang';
    }

    public function getClientKeyKonfirmasiAttribute(): string
    {
        return 'ULANG-'.$this->id.'-'.$this->jatuh_tempo->format('Ymd');
    }

    public function majukanJatuhTempo(): void
    {
        $berikutnya = match ($this->aturan) {
            'mingguan' => $this->jatuh_tempo->copy()->addWeek(),
            'tahunan' => $this->jatuh_tempo->copy()->addYearNoOverflow(),
            default => $this->jatuh_tempo->copy()->addMonthNoOverflow(),
        };

        $this->forceFill([
            'jatuh_tempo' => $berikutnya,
            'terakhir_dikonfirmasi' => Carbon::today(),
        ])->save();
    }
}
