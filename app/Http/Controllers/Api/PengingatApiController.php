<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KonfirmasiPengingatRequest;
use App\Http\Requests\Api\SimpanPengingatRequest;
use App\Http\Requests\Api\UpdatePengingatRequest;
use App\Http\Resources\PengingatResource;
use App\Http\Resources\TransaksiResource;
use App\Models\TransaksiBerulang;
use App\Services\PengingatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengingatApiController extends Controller
{
    public function index(Request $request, PengingatService $service): JsonResponse
    {
        $data = $service->daftar($request->boolean('semua'));

        return response()->json([
            'data' => PengingatResource::collection($data)->resolve($request),
            'server_time' => waktu_api(),
        ]);
    }

    public function store(SimpanPengingatRequest $request, PengingatService $service): JsonResponse
    {
        $jadwal = $service->simpan($request->validated());

        return response()->json([
            'data' => (new PengingatResource($jadwal))->resolve($request),
            'dibuat' => $jadwal->wasRecentlyCreated,
            'server_time' => waktu_api(),
        ], $jadwal->wasRecentlyCreated ? 201 : 200);
    }

    public function update(
        UpdatePengingatRequest $request,
        TransaksiBerulang $pengingat,
        PengingatService $service
    ): JsonResponse {
        $jadwal = $service->perbarui($pengingat, $request->validated());

        return response()->json([
            'data' => (new PengingatResource($jadwal))->resolve($request),
            'server_time' => waktu_api(),
        ]);
    }

    public function destroy(Request $request, string $pengingat, PengingatService $service): JsonResponse
    {
        $jadwal = TransaksiBerulang::withTrashed()->findOrFail((int) $pengingat);
        $jadwal = $service->hapus($jadwal);

        return response()->json([
            'data' => (new PengingatResource($jadwal))->resolve($request),
            'dihapus' => true,
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
