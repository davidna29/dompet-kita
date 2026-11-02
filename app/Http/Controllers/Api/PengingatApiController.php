<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KonfirmasiPengingatRequest;
use App\Http\Resources\TransaksiResource;
use App\Models\TransaksiBerulang;
use App\Services\PengingatService;
use Illuminate\Http\JsonResponse;

class PengingatApiController extends Controller
{
    public function index(PengingatService $service): JsonResponse
    {
        $data = $service->daftar()->map(fn (TransaksiBerulang $jadwal) => [
            'id' => $jadwal->id,
            'nama' => $jadwal->nama,
            'kategori' => $jadwal->kategori,
            'jumlah' => (int) $jadwal->jumlah,
            'aturan' => $jadwal->aturan,
            'jatuh_tempo' => $jadwal->jatuh_tempo->format('Y-m-d'),
            'metode' => $jadwal->metode,
            'kantong_tujuan' => $jadwal->kantong_tujuan,
            'pencatat' => $jadwal->pencatat,
            'aktif' => (bool) $jadwal->aktif,
            'status' => $jadwal->status,
            'client_key_konfirmasi' => $jadwal->client_key_konfirmasi,
            'terakhir_dikonfirmasi' => $jadwal->terakhir_dikonfirmasi?->format('Y-m-d'),
        ]);

        return response()->json([
            'data' => $data,
            'server_time' => waktu_api(),
        ]);
    }

    public function konfirmasi(
        KonfirmasiPengingatRequest $request,
        TransaksiBerulang $pengingat,
        PengingatService $service
    ): JsonResponse {
        [$transaksi, $dibuat] = $service->konfirmasi($pengingat, $request->validated(), 'telegram');

        return response()->json([
            'data' => (new TransaksiResource($transaksi))->resolve($request),
            'dibuat' => $dibuat,
            'server_time' => waktu_api(),
        ], $dibuat ? 201 : 200);
    }
}
