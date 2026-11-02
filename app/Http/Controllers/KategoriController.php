<?php

namespace App\Http\Controllers;

use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Support\Icon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(): View
    {
        $meta = KategoriMeta::query()->orderBy('urutan')->orderBy('kategori')->get()->keyBy('kategori');
        $pemakaian = Transaksi::query()
            ->selectRaw('kategori, COUNT(*) as jumlah_transaksi, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->get()
            ->keyBy('kategori');

        $nama = collect($pemakaian->keys())
            ->merge($meta->keys())
            ->unique()
            ->sort()
            ->values();

        $kategori = $nama->map(function (string $kategori) use ($meta, $pemakaian) {
            $m = $meta->get($kategori);

            return [
                'kategori' => $kategori,
                'ikon' => $m->ikon ?? Icon::untukKategori($kategori),
                'warna' => $m->warna ?? '#496976',
                'urutan' => $m->urutan ?? 0,
                'meta_id' => $m?->id,
                'jumlah_transaksi' => (int) ($pemakaian->get($kategori)->jumlah_transaksi ?? 0),
                'total' => (int) ($pemakaian->get($kategori)->total ?? 0),
            ];
        });

        return view('kategori.index', [
            'kategori' => $kategori,
            'icon' => array_keys(Icon::PATHS),
            'palet' => ['#A83B24', '#4F6B4A', '#496976', '#87570F', '#8C6A4A', '#7D7F5C', '#8E5F66', '#6B7A8F', '#A76B4A', '#657A5E'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kategori' => ['required', 'string', 'max:50'],
            'ikon' => ['required', 'string', 'max:40'],
            'warna' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $kategori = Str::title(trim($data['kategori']));
        if (mb_strtolower($kategori) === 'mutasi') {
            $kategori = 'Mutasi';
        }

        $ikon = Icon::ada($data['ikon']) ? $data['ikon'] : 'tag';

        $meta = KategoriMeta::withTrashed()->firstOrNew(['kategori' => $kategori]);
        $meta->fill([
            'ikon' => $ikon,
            'warna' => $data['warna'],
            'urutan' => (int) ($data['urutan'] ?? 0),
        ]);
        if ($meta->trashed()) {
            $meta->restore();
        }
        $meta->save();

        return redirect()
            ->route('kategori.index')
            ->with('sukses', 'Tampilan kategori disimpan.');
    }

    public function destroy(KategoriMeta $kategoriMeta): RedirectResponse
    {
        $kategoriMeta->delete();

        return redirect()
            ->route('kategori.index')
            ->with('sukses', 'Kategori kembali memakai ikon dan warna bawaan.');
    }
}
