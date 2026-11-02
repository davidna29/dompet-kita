<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FiltersTransaksi
{
    public static function dariRequest(Request $request, bool $defaultBulanIni = true): array
    {
        $semua = $request->boolean('semua');
        $dari = self::tanggal($request->query('dari'));
        $sampai = self::tanggal($request->query('sampai'));

        if (! $semua && $defaultBulanIni && $dari === null && $sampai === null) {
            $dari = now()->startOfMonth()->toDateString();
            $sampai = now()->endOfMonth()->toDateString();
        }

        $jenis = $request->query('jenis');
        if (! in_array($jenis, ['masuk', 'keluar'], true)) {
            $jenis = null;
        }

        return [
            'semua' => $semua,
            'dari' => $semua ? null : $dari,
            'sampai' => $semua ? null : $sampai,
            'jenis' => $jenis,
            'kategori' => self::teks($request->query('kategori')),
            'pencatat' => self::teks($request->query('pencatat')),
            'metode' => self::teks($request->query('metode')),
        ];
    }

    public static function terapkan(Builder $query, array $filter): Builder
    {
        if (! empty($filter['dari'])) {
            $query->where('tanggal', '>=', $filter['dari']);
        }
        if (! empty($filter['sampai'])) {
            $query->where('tanggal', '<=', $filter['sampai']);
        }
        if (! empty($filter['jenis'])) {
            $query->where('jenis', $filter['jenis']);
        }
        if (! empty($filter['kategori'])) {
            $query->where('kategori', $filter['kategori']);
        }
        if (! empty($filter['pencatat'])) {
            $query->where('pencatat', $filter['pencatat']);
        }
        if (! empty($filter['metode'])) {
            if ($filter['metode'] === '__none__') {
                $query->whereNull('metode');
            } else {
                $query->where('metode', $filter['metode']);
            }
        }

        return $query;
    }

    public static function label(array $filter): string
    {
        if (! empty($filter['metode']) && $filter['metode'] === '__none__') {
            return 'Kantong belum dipilih';
        }

        if (! empty($filter['dari']) && ! empty($filter['sampai'])) {
            return tanggal_id($filter['dari']).' – '.tanggal_id($filter['sampai']);
        }

        if (! empty($filter['dari'])) {
            return 'Sejak '.tanggal_id($filter['dari']);
        }

        if (! empty($filter['sampai'])) {
            return 'Sampai '.tanggal_id($filter['sampai']);
        }

        return 'Semua waktu';
    }

    private static function tanggal(mixed $nilai): ?string
    {
        if (! is_string($nilai) || trim($nilai) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $nilai)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function teks(mixed $nilai): ?string
    {
        if (! is_string($nilai)) {
            return null;
        }

        $teks = trim($nilai);

        return $teks !== '' ? $teks : null;
    }
}
