<?php

namespace App\Services;

use App\Models\Transaksi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class RingkasanService
{
    public function ringkasan(Carbon $mulai, Carbon $selesai): array
    {
        $mulaiStr = $mulai->copy()->startOfDay()->toDateString();
        $selesaiStr = $selesai->copy()->endOfDay()->toDateString();

        $dasar = Transaksi::query()->whereBetween('tanggal', [$mulaiStr, $selesaiStr]);

        $masuk = (clone $dasar)->where('jenis', 'masuk')->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)->sum('jumlah');
        $keluar = (clone $dasar)->where('jenis', 'keluar')->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)->sum('jumlah');
        $mutasi = (clone $dasar)->where('kategori', 'Mutasi')->sum('jumlah');
        $hutang = (clone $dasar)->where('jenis', 'keluar')->where('kategori', 'Hutang')->sum('jumlah');
        $piutang = (clone $dasar)->where('jenis', 'masuk')->where('kategori', 'Piutang')->sum('jumlah');

        $perKategori = (clone $dasar)
            ->where('jenis', 'keluar')
            ->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)
            ->selectRaw('kategori, SUM(jumlah) as total, COUNT(*) as jumlah_transaksi')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($baris) => [
                'kategori' => $baris->kategori,
                'total' => (int) $baris->total,
                'jumlah_transaksi' => (int) $baris->jumlah_transaksi,
            ])
            ->all();

        $perPencatat = (clone $dasar)
            ->where('jenis', 'keluar')
            ->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)
            ->selectRaw('pencatat, SUM(jumlah) as total, COUNT(*) as jumlah_transaksi')
            ->groupBy('pencatat')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($baris) => [
                'pencatat' => $baris->pencatat,
                'total' => (int) $baris->total,
                'jumlah_transaksi' => (int) $baris->jumlah_transaksi,
            ])
            ->all();

        return [
            'mulai' => $mulaiStr,
            'selesai' => $selesaiStr,
            'masuk' => (int) $masuk,
            'keluar' => (int) $keluar,
            'mutasi' => (int) $mutasi,
            'hutang' => (int) $hutang,
            'piutang' => (int) $piutang,
            'selisih' => (int) $masuk - (int) $keluar,
            'jumlah_transaksi' => (clone $dasar)->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)->count(),
            'jumlah_mutasi' => (clone $dasar)->where('kategori', 'Mutasi')->count(),
            'per_kategori' => $perKategori,
            'per_pencatat' => $perPencatat,
        ];
    }

    public function ringkasanBulan(Carbon $bulan): array
    {
        return $this->ringkasan(
            $bulan->copy()->startOfMonth(),
            $bulan->copy()->endOfMonth()
        );
    }

    public function trenBulanan(Carbon $bulanAkhir, int $jumlahBulan = 6): array
    {
        $hasil = [];

        for ($i = $jumlahBulan - 1; $i >= 0; $i--) {
            $bulan = $bulanAkhir->copy()->startOfMonth()->subMonths($i);
            $ringkasan = $this->ringkasanBulan($bulan);

            $hasil[] = [
                'bulan' => $bulan->format('Y-m'),
                'label' => bulan_singkat($bulan),
                'label_panjang' => bulan_id($bulan),
                'total' => $ringkasan['keluar'],
                'masuk' => $ringkasan['masuk'],
                'mutasi' => $ringkasan['mutasi'],
            ];
        }

        return $hasil;
    }

    public function kumulatifHarian(Carbon $mulai, Carbon $selesai): array
    {
        $mulaiStr = $mulai->copy()->startOfDay()->toDateString();
        $selesaiStr = $selesai->copy()->endOfDay()->toDateString();

        $harian = Transaksi::query()
            ->whereBetween('tanggal', [$mulaiStr, $selesaiStr])
            ->where('jenis', 'keluar')
            ->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)
            ->selectRaw('DATE(tanggal) as hari, SUM(jumlah) as total')
            ->groupBy('hari')
            ->pluck('total', 'hari');

        $hasil = [];
        $kumulatif = 0;

        foreach (CarbonPeriod::create($mulai->copy()->startOfDay(), $selesai->copy()->startOfDay()) as $hari) {
            $kunci = $hari->toDateString();
            $nilai = (int) ($harian[$kunci] ?? 0);
            $kumulatif += $nilai;

            $hasil[] = [
                'tanggal' => $kunci,
                'label' => $hari->format('j M'),
                'harian' => $nilai,
                'kumulatif' => $kumulatif,
            ];
        }

        return $hasil;
    }

    public function perbandinganBulanLalu(Carbon $bulan): array
    {
        $sekarang = $this->ringkasanBulan($bulan);
        $lalu = $this->ringkasanBulan($bulan->copy()->subMonthNoOverflow());

        $persen = null;
        if ($lalu['keluar'] > 0) {
            $persen = (int) round((($sekarang['keluar'] - $lalu['keluar']) / $lalu['keluar']) * 100);
        }

        return [
            'sekarang' => $sekarang['keluar'],
            'sebelumnya' => $lalu['keluar'],
            'persen' => $persen,
            'bulan_sebelumnya' => $bulan->copy()->subMonthNoOverflow()->format('Y-m'),
        ];
    }

    public function ringkasanUntukFilter(array $filter): array
    {
        $query = Transaksi::query();

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

        $masuk = (clone $query)->where('jenis', 'masuk')->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)->sum('jumlah');
        $keluar = (clone $query)->where('jenis', 'keluar')->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)->sum('jumlah');
        $mutasi = (clone $query)->where('kategori', 'Mutasi')->sum('jumlah');
        $hutang = (clone $query)->where('jenis', 'keluar')->where('kategori', 'Hutang')->sum('jumlah');
        $piutang = (clone $query)->where('jenis', 'masuk')->where('kategori', 'Piutang')->sum('jumlah');

        return [
            'masuk' => (int) $masuk,
            'keluar' => (int) $keluar,
            'mutasi' => (int) $mutasi,
            'hutang' => (int) $hutang,
            'piutang' => (int) $piutang,
            'selisih' => (int) $masuk - (int) $keluar,
            'jumlah' => (clone $query)->count(),
        ];
    }

    public function kategoriDenganMeta(): Collection
    {
        return Transaksi::query()
            ->select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
    }
}
