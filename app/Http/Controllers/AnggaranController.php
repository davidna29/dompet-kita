<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\Transaksi;
use App\Services\AnggaranService;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggaranController extends Controller
{
    public function index(Request $request, AnggaranService $service): View
    {
        $periode = $this->periode($request->query('periode'));
        $hasil = $service->untukPeriode($periode);

        $kategori = Transaksi::query()
            ->where('kategori', '!=', 'Mutasi')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');

        return view('anggaran.index', [
            'periode' => $periode,
            'hasil' => $hasil,
            'kategori' => $kategori,
            'bulanSebelum' => Carbon::createFromFormat('Y-m', $periode)->subMonthNoOverflow()->format('Y-m'),
            'bulanBerikut' => Carbon::createFromFormat('Y-m', $periode)->addMonthNoOverflow()->format('Y-m'),
            'bulanBerjalan' => $periode === now()->format('Y-m'),
        ]);
    }

    public function store(Request $request, AnggaranService $service): RedirectResponse
    {
        $data = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'kategori' => ['nullable', 'string', 'max:50'],
            'jumlah' => ['required', 'string', 'max:30'],
            'ambang' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $jumlah = JumlahParser::parse($data['jumlah']);
        if ($jumlah === null) {
            return back()->withErrors(['jumlah' => 'Anggaran harus bilangan bulat positif.'])->withInput();
        }

        $kategori = $data['kategori'] ?? null;
        if ($kategori === '__total__') {
            $kategori = null;
        }

        $service->simpan($data['periode'], $kategori, $jumlah, (int) ($data['ambang'] ?? 80));

        return redirect()
            ->route('anggaran.index', ['periode' => $data['periode']])
            ->with('sukses', 'Anggaran disimpan.');
    }

    public function destroy(Anggaran $anggaran, AnggaranService $service): RedirectResponse
    {
        $periode = $anggaran->periode;
        $service->hapus($anggaran);

        return redirect()
            ->route('anggaran.index', ['periode' => $periode])
            ->with('sukses', 'Anggaran dihapus.');
    }

    public function salin(Request $request, AnggaranService $service): RedirectResponse
    {
        $data = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ]);

        $jumlah = $service->salinDariBulanLalu($data['periode']);

        return redirect()
            ->route('anggaran.index', ['periode' => $data['periode']])
            ->with('sukses', $jumlah > 0
                ? "{$jumlah} anggaran disalin dari bulan sebelumnya."
                : 'Tidak ada anggaran lama yang bisa disalin.');
    }

    private function periode(mixed $nilai): string
    {
        if (is_string($nilai) && preg_match('/^\d{4}-\d{2}$/', $nilai)) {
            try {
                Carbon::createFromFormat('Y-m', $nilai);

                return $nilai;
            } catch (\Throwable $e) {
                // Fall through.
            }
        }

        return now()->format('Y-m');
    }
}
