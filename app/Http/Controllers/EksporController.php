<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Support\FiltersTransaksi;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EksporController extends Controller
{
    public function transaksi(Request $request): StreamedResponse
    {
        $filter = FiltersTransaksi::dariRequest($request);
        $query = FiltersTransaksi::terapkan(Transaksi::query(), $filter)
            ->orderBy('tanggal')
            ->orderBy('id');

        $nama = 'dompet-kita-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $keluar = fopen('php://output', 'w');
            fwrite($keluar, "\xEF\xBB\xBF");
            fputcsv($keluar, ['tanggal', 'jenis', 'kategori', 'jumlah', 'keterangan', 'pencatat', 'metode', 'kantong_tujuan']);

            $query->chunk(500, function ($rows) use ($keluar) {
                foreach ($rows as $transaksi) {
                    fputcsv($keluar, [
                        $transaksi->tanggal->format('Y-m-d'),
                        $transaksi->jenis,
                        $transaksi->kategori,
                        (int) $transaksi->jumlah,
                        $transaksi->keterangan ?? '',
                        $transaksi->pencatat,
                        $transaksi->metode ?? '',
                        $transaksi->kantong_tujuan ?? '',
                    ]);
                }
            });

            fclose($keluar);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
