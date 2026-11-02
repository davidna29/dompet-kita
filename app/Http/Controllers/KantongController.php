<?php

namespace App\Http\Controllers;

use App\Models\Kantong;
use App\Models\Transaksi;
use App\Services\SaldoService;
use App\Support\JumlahParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KantongController extends Controller
{
    public function index(Request $request, SaldoService $saldoService): View
    {
        $kantong = $saldoService->semua();
        $namaKantong = Kantong::query()->pluck('nama')->all();

        $metodeTidakTerdaftar = Transaksi::query()
            ->whereNotNull('metode')
            ->whereNotIn('metode', $namaKantong)
            ->selectRaw('metode, COUNT(*) as jumlah, SUM(jumlah) as total')
            ->groupBy('metode')
            ->orderByDesc('total')
            ->get();

        return view('kantong', [
            'kantong' => $kantong,
            'mutasi' => $saldoService->mutasiTerbaru(8),
            'tanpaKantong' => $saldoService->tanpaKantong(),
            'metodeTidakTerdaftar' => $metodeTidakTerdaftar,
            'total' => $kantong->sum('saldo'),
            'rekonsiliasi' => session('rekonsiliasi'),
        ]);
    }

    public function rekonsiliasi(Request $request, SaldoService $saldoService): RedirectResponse
    {
        $data = $request->validate([
            'saldo' => ['nullable', 'array'],
            'saldo.*' => ['nullable', 'string', 'max:30'],
        ]);

        $hasil = $saldoService->rekonsiliasi($data['saldo'] ?? []);

        return redirect()
            ->route('kantong')
            ->with('rekonsiliasi', $hasil)
            ->with('sukses', 'Selisih dihitung. Tidak ada penyesuaian otomatis yang dicatat.');
    }

    public function simpanSaldoAwal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'saldo_awal' => ['required', 'array'],
            'saldo_awal.*' => ['required', 'string', 'max:30'],
            'tanggal_saldo_awal' => ['required', 'array'],
            'tanggal_saldo_awal.*' => ['required', 'date_format:Y-m-d'],
        ]);

        foreach (Kantong::query()->get() as $kantong) {
            $nilai = JumlahParser::parseNonNegative($data['saldo_awal'][$kantong->id] ?? null);
            if ($nilai === null) {
                return back()->withErrors([
                    'saldo_awal' => 'Saldo awal '.$kantong->nama.' harus bilangan bulat positif atau nol.',
                ])->withInput();
            }

            $kantong->update([
                'saldo_awal' => $nilai,
                'tanggal_saldo_awal' => $data['tanggal_saldo_awal'][$kantong->id] ?? $kantong->tanggal_saldo_awal,
            ]);
        }

        return redirect()
            ->route('kantong')
            ->with('sukses', 'Saldo awal dan tanggal hitung diperbarui.');
    }
}
