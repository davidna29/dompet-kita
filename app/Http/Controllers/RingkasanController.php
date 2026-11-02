<?php

namespace App\Http\Controllers;

use App\Models\KategoriMeta;
use App\Services\AnggaranService;
use App\Services\RingkasanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RingkasanController extends Controller
{
    public function index(
        Request $request,
        RingkasanService $ringkasanService,
        AnggaranService $anggaranService
    ): View {
        $preset = $request->query('periode', 'bulan');
        if (! in_array($preset, ['hari', 'minggu', 'bulan', 'rentang'], true)) {
            $preset = 'bulan';
        }

        $bulan = $this->bulan($request->query('bulan'));
        [$mulai, $selesai] = $this->rentang($preset, $request, $bulan);

        $ringkasan = $ringkasanService->ringkasan($mulai, $selesai);
        $kumulatif = $ringkasanService->kumulatifHarian($mulai, $selesai);
        $tren = $ringkasanService->trenBulanan($bulan, 6);
        $perbandingan = $preset === 'bulan' ? $ringkasanService->perbandinganBulanLalu($bulan) : null;
        $anggaran = $anggaranService->untukPeriode($bulan->format('Y-m'));

        $kategoriMeta = KategoriMeta::query()->get()->keyBy('kategori');
        $palet = ['#A83B24', '#4F6B4A', '#496976', '#87570F', '#8C6A4A', '#7D7F5C', '#8E5F66', '#6B7A8F'];

        $donat = collect($ringkasan['per_kategori'])->take(8)->values()->map(function ($baris, $index) use ($kategoriMeta, $palet) {
            return [
                'label' => $baris['kategori'],
                'total' => $baris['total'],
                'warna' => $kategoriMeta->get($baris['kategori'])?->warna ?? $palet[$index % count($palet)],
            ];
        })->all();

        return view('ringkasan', [
            'preset' => $preset,
            'bulan' => $bulan,
            'mulai' => $mulai,
            'selesai' => $selesai,
            'ringkasan' => $ringkasan,
            'kumulatif' => $kumulatif,
            'tren' => $tren,
            'perbandingan' => $perbandingan,
            'anggaran' => $anggaran,
            'kategoriMeta' => $kategoriMeta,
            'donat' => $donat,
            'filterExport' => [
                'dari' => $mulai->toDateString(),
                'sampai' => $selesai->toDateString(),
            ],
            'bulanSebelum' => $bulan->copy()->subMonthNoOverflow()->format('Y-m'),
            'bulanBerikut' => $bulan->copy()->addMonthNoOverflow()->format('Y-m'),
            'bulanBerjalan' => $bulan->isSameMonth(today()),
        ]);
    }

    private function bulan(mixed $nilai): Carbon
    {
        if (is_string($nilai) && preg_match('/^\d{4}-\d{2}$/', $nilai)) {
            try {
                return Carbon::createFromFormat('Y-m', $nilai)->startOfMonth();
            } catch (\Throwable $e) {
                // Fall through.
            }
        }

        return today()->startOfMonth();
    }

    private function rentang(string $preset, Request $request, Carbon $bulan): array
    {
        return match ($preset) {
            'hari' => [today()->startOfDay(), today()->endOfDay()],
            'minggu' => [today()->startOfWeek(Carbon::MONDAY), today()->endOfWeek(Carbon::SUNDAY)],
            'rentang' => [
                $this->tanggal($request->query('dari')) ?? $bulan->copy()->startOfMonth(),
                $this->tanggal($request->query('sampai')) ?? $bulan->copy()->endOfMonth(),
            ],
            default => [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()],
        };
    }

    private function tanggal(mixed $nilai): ?Carbon
    {
        if (! is_string($nilai) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $nilai)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
