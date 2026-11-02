<?php

namespace App\Services;

use App\Models\Kantong;
use App\Models\Transaksi;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SaldoService
{
    public function semua(?Carbon $sampai = null): Collection
    {
        $sampaiStr = ($sampai ?? Carbon::today())->toDateString();

        return Kantong::query()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->map(fn (Kantong $kantong) => $this->hitung($kantong, $sampaiStr));
    }

    public function arsip(?Carbon $sampai = null): Collection
    {
        $sampaiStr = ($sampai ?? Carbon::today())->toDateString();

        return Kantong::onlyTrashed()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->map(fn (Kantong $kantong) => [
                ...$this->hitung($kantong, $sampaiStr),
                'diarsipkan_pada' => $kantong->deleted_at?->format('Y-m-d'),
            ]);
    }

    public function hitung(Kantong $kantong, ?string $sampai = null): array
    {
        $sampai ??= Carbon::today()->toDateString();
        $mulai = $kantong->tanggal_saldo_awal->toDateString();

        $dasar = Transaksi::query()
            ->where('tanggal', '>=', $mulai)
            ->where('tanggal', '<=', $sampai);

        $masuk = (clone $dasar)
            ->where('jenis', 'masuk')
            ->where('metode', $kantong->nama)
            ->sum('jumlah');

        $keluar = (clone $dasar)
            ->where('jenis', 'keluar')
            ->where('metode', $kantong->nama)
            ->sum('jumlah');

        $terimaMutasi = (clone $dasar)
            ->where('kategori', 'Mutasi')
            ->where('kantong_tujuan', $kantong->nama)
            ->sum('jumlah');

        $saldo = (int) $kantong->saldo_awal + (int) $masuk - (int) $keluar + (int) $terimaMutasi;

        return [
            'id' => $kantong->id,
            'nama' => $kantong->nama,
            'saldo_awal' => (int) $kantong->saldo_awal,
            'tanggal_saldo_awal' => $kantong->tanggal_saldo_awal->format('Y-m-d'),
            'masuk' => (int) $masuk,
            'keluar' => (int) $keluar,
            'terima_mutasi' => (int) $terimaMutasi,
            'saldo' => (int) $saldo,
            'urutan' => (int) $kantong->urutan,
        ];
    }

    public function total(?Collection $kantong = null): int
    {
        return (int) ($kantong ?? $this->semua())->sum('saldo');
    }

    public function tanpaKantong(): array
    {
        $query = Transaksi::query()->whereNull('metode');

        return [
            'jumlah' => (clone $query)->count(),
            'total' => (int) (clone $query)->sum('jumlah'),
        ];
    }

    public function mutasiTerbaru(int $limit = 6): Collection
    {
        return Transaksi::query()
            ->mutasi()
            ->latest('tanggal')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Compare the server-computed balance with a real balance entered by the user.
     * The difference is displayed only; it is never written as an adjustment.
     */
    public function rekonsiliasi(array $saldoNyata): array
    {
        $hasil = [];

        foreach ($this->semua() as $kantong) {
            $input = $saldoNyata[$kantong['id']] ?? null;
            $nyata = JumlahParser::parse($input);

            $hasil[] = [
                ...$kantong,
                'saldo_nyata' => $nyata,
                'selisih' => $nyata === null ? null : $kantong['saldo'] - $nyata,
            ];
        }

        return $hasil;
    }
}
