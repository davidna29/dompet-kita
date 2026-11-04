<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Support\Icon;
use Illuminate\Http\JsonResponse;

class KategoriApiController extends Controller
{
    public function index(): JsonResponse
    {
        $meta = KategoriMeta::query()->get()->keyBy('kategori');

        $pemakaian = Transaksi::query()
            ->selectRaw('kategori, jenis, COUNT(*) as jumlah_transaksi, SUM(jumlah) as total')
            ->groupBy('kategori', 'jenis')
            ->get()
            ->groupBy('kategori');

        $nama = $pemakaian->keys()
            ->merge($meta->keys())
            ->unique()
            ->sort()
            ->values();

        $data = $nama->map(function (string $kategori) use ($pemakaian, $meta) {
            $m = $meta->get($kategori);
            $baris = $pemakaian->get($kategori, collect());
            $masuk = $baris->firstWhere('jenis', 'masuk');
            $keluar = $baris->firstWhere('jenis', 'keluar');

            return [
                'kategori' => $kategori,
                'ikon' => $m->ikon ?? Icon::untukKategori($kategori),
                'warna' => $m->warna ?? '#496976',
                'urutan' => (int) ($m->urutan ?? 0),
                'ada_meta' => $m !== null,
                'mutasi' => $kategori === 'Mutasi',
                'jumlah_transaksi' => (int) (($masuk->jumlah_transaksi ?? 0) + ($keluar->jumlah_transaksi ?? 0)),
                'total' => (int) (($masuk->total ?? 0) + ($keluar->total ?? 0)),
                'masuk' => [
                    'jumlah_transaksi' => (int) ($masuk->jumlah_transaksi ?? 0),
                    'total' => (int) ($masuk->total ?? 0),
                ],
                'keluar' => [
                    'jumlah_transaksi' => (int) ($keluar->jumlah_transaksi ?? 0),
                    'total' => (int) ($keluar->total ?? 0),
                ],
            ];
        });

        return response()->json([
            'data' => $data->values(),
            'server_time' => waktu_api(),
        ]);
    }
}
