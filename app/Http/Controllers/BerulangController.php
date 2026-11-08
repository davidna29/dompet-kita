<?php

namespace App\Http\Controllers;

use App\Models\Kantong;
use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Models\User;
use App\Services\PengingatService;
use App\Support\JumlahParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BerulangController extends Controller
{
    public function index(PengingatService $service): View
    {
        return view('berulang.index', [
            'jadwal' => $service->daftar(),
            'jatuhTempo' => $service->jatuhTempo(10),
        ]);
    }

    public function create(): View
    {
        return view('berulang.form', [
            'jadwal' => null,
            'pencatat' => User::query()->orderBy('name')->pluck('pencatat')->filter()->values(),
            'kategori' => $this->kategori(),
            'kantong' => Kantong::query()->orderBy('urutan')->get(),
        ]);
    }

    public function store(Request $request, PengingatService $service): RedirectResponse
    {
        $service->simpan($this->validated($request));

        return redirect()
            ->route('berulang.index')
            ->with('sukses', 'Jadwal berulang ditambahkan.');
    }

    public function edit(TransaksiBerulang $berulang): View
    {
        return view('berulang.form', [
            'jadwal' => $berulang,
            'pencatat' => User::query()->orderBy('name')->pluck('pencatat')->filter()->values(),
            'kategori' => $this->kategori(),
            'kantong' => Kantong::query()->orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, TransaksiBerulang $berulang, PengingatService $service): RedirectResponse
    {
        $service->perbarui($berulang, $this->validated($request));

        return redirect()
            ->route('berulang.index')
            ->with('sukses', 'Jadwal berulang diperbarui.');
    }

    public function destroy(TransaksiBerulang $berulang): RedirectResponse
    {
        $berulang->delete();

        return redirect()
            ->route('berulang.index')
            ->with('sukses', 'Jadwal berulang dihapus. Riwayat transaksinya tetap ada.');
    }

    public function konfirmasi(Request $request, TransaksiBerulang $berulang, PengingatService $service): RedirectResponse
    {
        $data = $request->validate([
            'jumlah' => ['nullable', 'string', 'max:30'],
            'jatuh_tempo' => ['nullable', 'date_format:Y-m-d'],
        ]);

        [$transaksi, $dibuat] = $service->konfirmasi($berulang, [
            'jumlah' => $data['jumlah'] ?? null,
            'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
        ], 'ui', $request->user());

        return redirect()
            ->route('berulang.index')
            ->with('transaksi_baru_id', $transaksi->id)
            ->with('sukses', $dibuat
                ? 'Tagihan dikonfirmasi dan transaksi dibuat.'
                : 'Jatuh tempo ini sudah dikonfirmasi sebelumnya.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:80'],
            'kategori' => ['required', 'string', 'max:50'],
            'jumlah' => ['required', 'string', 'max:30'],
            'aturan' => ['required', 'in:bulanan,mingguan,tahunan'],
            'jatuh_tempo' => ['required', 'date_format:Y-m-d'],
            'metode' => ['nullable', 'string', 'max:30'],
            'kantong_tujuan' => ['nullable', 'string', 'max:30'],
            'pencatat' => ['required', 'string', 'max:50'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        $jumlah = JumlahParser::parse($data['jumlah']);
        if ($jumlah === null) {
            throw ValidationException::withMessages(['jumlah' => 'Jumlah harus bilangan bulat positif.']);
        }

        $kategori = mb_convert_case(trim($data['kategori']), MB_CASE_TITLE, 'UTF-8');
        if ($kategori === 'Mutasi') {
            if (empty($data['metode']) || empty($data['kantong_tujuan']) || $data['metode'] === $data['kantong_tujuan']) {
                throw ValidationException::withMessages([
                    'kantong_tujuan' => 'Mutasi membutuhkan kantong asal dan tujuan yang berbeda.',
                ]);
            }
        } else {
            $data['kantong_tujuan'] = null;
        }

        return [
            'nama' => trim($data['nama']),
            'kategori' => $kategori,
            'jumlah' => $jumlah,
            'aturan' => $data['aturan'],
            'jatuh_tempo' => $data['jatuh_tempo'],
            'metode' => $data['metode'] ?? null,
            'kantong_tujuan' => $data['kantong_tujuan'] ?? null,
            'pencatat' => $data['pencatat'],
            'aktif' => $request->boolean('aktif'),
        ];
    }

    private function kategori(): Collection
    {
        return Transaksi::query()
            ->whereNotIn('kategori', Transaksi::KATEGORI_TRANSFER)
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori')
            ->merge(['Tagihan', 'Pendidikan', 'Kesehatan', 'Rumah'])
            ->unique()
            ->sort()
            ->values();
    }
}
