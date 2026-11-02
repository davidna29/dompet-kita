<?php

namespace App\Http\Controllers;

use App\Models\Kantong;
use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Services\KantongService;
use App\Services\SaldoService;
use App\Support\JumlahParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KantongController extends Controller
{
    public function index(Request $request, SaldoService $saldoService): View
    {
        $jadwalAktif = TransaksiBerulang::query()
            ->where('aktif', true)
            ->get(['metode', 'kantong_tujuan'])
            ->flatMap(fn (TransaksiBerulang $jadwal) => [$jadwal->metode, $jadwal->kantong_tujuan])
            ->filter()
            ->unique()
            ->values();

        $kantong = $saldoService->semua()->map(function (array $baris) use ($jadwalAktif) {
            $baris['alasan_arsip'] = $baris['saldo'] !== 0
                ? 'Saldo harus nol sebelum kantong diarsipkan.'
                : ($jadwalAktif->contains($baris['nama'])
                    ? 'Ubah atau nonaktifkan jadwal berulang yang memakai kantong ini.'
                    : null);

            return $baris;
        });
        $arsip = $saldoService->arsip();
        $namaKantong = Kantong::withTrashed()->pluck('nama')->all();

        $metodeTidakTerdaftar = Transaksi::query()
            ->whereNotNull('metode')
            ->whereNotIn('metode', $namaKantong)
            ->selectRaw('metode, COUNT(*) as jumlah, SUM(jumlah) as total')
            ->groupBy('metode')
            ->orderByDesc('total')
            ->get();

        return view('kantong', [
            'kantong' => $kantong,
            'arsip' => $arsip,
            'mutasi' => $saldoService->mutasiTerbaru(8),
            'tanpaKantong' => $saldoService->tanpaKantong(),
            'metodeTidakTerdaftar' => $metodeTidakTerdaftar,
            'total' => $kantong->sum('saldo'),
            'rekonsiliasi' => session('rekonsiliasi'),
        ]);
    }

    public function create(): View
    {
        return view('kantong.form', ['kantong' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['nama' => trim((string) $request->input('nama'))]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:30', Rule::unique('kantong', 'nama')],
            'saldo_awal' => ['required', 'string', 'max:30'],
            'tanggal_saldo_awal' => ['required', 'date_format:Y-m-d'],
        ], [
            'nama.unique' => 'Nama kantong sudah digunakan atau masih dimiliki kantong arsip. Pulihkan kantong tersebut atau pilih nama lain.',
        ]);

        $saldoAwal = JumlahParser::parseNonNegative($data['saldo_awal']);
        if ($saldoAwal === null) {
            return back()->withErrors([
                'saldo_awal' => 'Saldo awal harus bilangan bulat positif atau nol.',
            ])->withInput();
        }

        Kantong::create([
            'nama' => trim($data['nama']),
            'saldo_awal' => $saldoAwal,
            'tanggal_saldo_awal' => $data['tanggal_saldo_awal'],
            'urutan' => ((int) Kantong::withTrashed()->max('urutan')) + 1,
        ]);

        return redirect()
            ->route('kantong')
            ->with('sukses', 'Kantong baru ditambahkan.');
    }

    public function edit(Kantong $kantong): View
    {
        return view('kantong.form', ['kantong' => $kantong]);
    }

    public function update(Request $request, Kantong $kantong, KantongService $service): RedirectResponse
    {
        $request->merge(['nama' => trim((string) $request->input('nama'))]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:30', Rule::unique('kantong', 'nama')->ignore($kantong->id)],
        ], [
            'nama.unique' => 'Nama kantong sudah digunakan atau masih dimiliki kantong arsip. Pulihkan kantong tersebut atau pilih nama lain.',
        ]);

        $service->gantiNama($kantong, trim($data['nama']), $request->user());

        return redirect()
            ->route('kantong')
            ->with('sukses', 'Nama kantong dan referensi terkait diperbarui.');
    }

    public function destroy(Kantong $kantong, KantongService $service): RedirectResponse
    {
        $service->arsipkan($kantong);

        return redirect()
            ->route('kantong')
            ->with('sukses', 'Kantong diarsipkan. Riwayatnya tetap tersimpan.');
    }

    public function pulihkan(int $id): RedirectResponse
    {
        $kantong = Kantong::onlyTrashed()->findOrFail($id);
        $kantong->restore();

        return redirect()
            ->route('kantong')
            ->with('sukses', 'Kantong dipulihkan.');
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
