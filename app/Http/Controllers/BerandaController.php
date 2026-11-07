<?php

namespace App\Http\Controllers;

use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Services\AnggaranService;
use App\Services\PengingatService;
use App\Services\RingkasanService;
use App\Services\SaldoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BerandaController extends Controller
{
    public function __invoke(
        Request $request,
        RingkasanService $ringkasanService,
        SaldoService $saldoService,
        AnggaranService $anggaranService,
        PengingatService $pengingatService
    ): View {
        $bulan = $this->bulan($request->query('bulan'));
        $ringkasan = $ringkasanService->ringkasanBulan($bulan);
        $tren = $ringkasanService->trenBulanan($bulan, 6);
        $perbandingan = $ringkasanService->perbandinganBulanLalu($bulan);

        $kantong = $saldoService->semua();
        $tanpaKantong = $saldoService->tanpaKantong();
        $anggaran = $anggaranService->untukPeriode($bulan->format('Y-m'));

        $transaksi = Transaksi::query()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(7)
            ->get();

        $pengingat = $pengingatService->jatuhTempo(10);
        $kategoriMeta = KategoriMeta::query()->get()->keyBy('kategori');
        $jam = (int) now()->format('G');
        $sapaan = match (true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };

        return view('beranda', [
            'bulan' => $bulan,
            'ringkasan' => $ringkasan,
            'tren' => $tren,
            'perbandingan' => $perbandingan,
            'kantong' => $kantong,
            'tanpaKantong' => $tanpaKantong,
            'anggaran' => $anggaran,
            'transaksi' => $transaksi,
            'pengingat' => $pengingat,
            'sapaan' => $sapaan,
            'bulanSebelum' => $bulan->copy()->subMonthNoOverflow()->format('Y-m'),
            'bulanBerikut' => $bulan->copy()->addMonthNoOverflow()->format('Y-m'),
            'bulanBerjalan' => $bulan->isSameMonth(today()),
            'transaksiBaruId' => session('transaksi_baru_id'),
            'totalKantong' => $kantong->sum('saldo'),
            'totalKantongTipe' => $saldoService->totalByTipe($kantong),
            'kategoriMeta' => $kategoriMeta,
        ]);
    }

    private function bulan(mixed $nilai): Carbon
    {
        if (is_string($nilai) && preg_match('/^\d{4}-\d{2}$/', $nilai)) {
            try {
                $bulan = Carbon::createFromFormat('Y-m', $nilai)->startOfMonth();
                if ($bulan->lte(today()->startOfMonth())) {
                    return $bulan;
                }
            } catch (\Throwable $e) {
                // Fall through to the current month.
            }
        }

        return today()->startOfMonth();
    }
}
