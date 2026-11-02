<?php

namespace App\Http\Controllers;

use App\Models\Kantong;
use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\RingkasanService;
use App\Services\TransaksiService;
use App\Support\FiltersTransaksi;
use App\Support\Icon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(Request $request, RingkasanService $ringkasanService): View
    {
        $filter = FiltersTransaksi::dariRequest($request);

        $query = FiltersTransaksi::terapkan(Transaksi::query(), $filter);

        $transaksi = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $ringkasan = $ringkasanService->ringkasanUntukFilter($filter);

        $kategori = $ringkasanService->kategoriDenganMeta();
        $kategoriMeta = KategoriMeta::query()->get()->keyBy('kategori');
        $pencatat = User::query()->orderBy('name')->pluck('pencatat')->filter()->values();
        $kantong = Kantong::query()->orderBy('urutan')->get();

        return view('transaksi.index', [
            'transaksi' => $transaksi,
            'ringkasan' => $ringkasan,
            'filter' => $filter,
            'labelFilter' => FiltersTransaksi::label($filter),
            'kategori' => $kategori,
            'kategoriMeta' => $kategoriMeta,
            'pencatat' => $pencatat,
            'kantong' => $kantong,
        ]);
    }

    public function create(): View
    {
        return view('transaksi.form', [
            'transaksi' => null,
            'kantong' => Kantong::query()->orderBy('urutan')->get(),
        ]);
    }

    public function edit(Transaksi $transaksi): View
    {
        return view('transaksi.form', [
            'transaksi' => $transaksi->load('riwayat'),
            'kantong' => Kantong::query()->orderBy('urutan')->get(),
        ]);
    }

    public function destroy(Request $request, Transaksi $transaksi, TransaksiService $service): RedirectResponse
    {
        $service->hapus($transaksi, $request->user());

        return redirect()
            ->route('transaksi.index')
            ->with('sukses', 'Transaksi dihapus. Baris tetap tersimpan untuk sinkronisasi.');
    }
}
