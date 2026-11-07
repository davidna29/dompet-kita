<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use SoftDeletes;

    /**
     * Categories that move money without being household spending or income.
     */
    public const KATEGORI_TRANSFER = ['Mutasi', 'Hutang', 'Piutang'];

    protected $table = 'transaksi';

    protected $fillable = [
        'tanggal',
        'jenis',
        'kategori',
        'jumlah',
        'keterangan',
        'pencatat',
        'metode',
        'sumber',
        'client_key',
        'kantong_tujuan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(TransaksiRiwayat::class, 'transaksi_id')->latest('created_at')->latest('id');
    }

    public function setTanggalAttribute(mixed $nilai): void
    {
        $this->attributes['tanggal'] = $nilai instanceof \DateTimeInterface
            ? $nilai->format('Y-m-d')
            : Carbon::parse((string) $nilai)->toDateString();
    }

    public function scopePeriode(Builder $query, string $mulai, string $selesai): Builder
    {
        return $query->whereBetween('tanggal', [$mulai, $selesai]);
    }

    public function scopeBelanja(Builder $query): Builder
    {
        return $query->whereNotIn('kategori', self::KATEGORI_TRANSFER);
    }

    public function scopeMutasi(Builder $query): Builder
    {
        return $query->where('kategori', 'Mutasi');
    }

    public function getIsMutasiAttribute(): bool
    {
        return $this->kategori === 'Mutasi';
    }

    public function getTandaJumlahAttribute(): string
    {
        return $this->jenis === 'masuk' ? '+' : '−';
    }
}
