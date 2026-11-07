<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BayarHutangRequest;
use App\Http\Requests\Api\StoreHutangRequest;
use App\Http\Resources\HutangResource;
use App\Http\Resources\TransaksiResource;
use App\Models\HutangPiutang;
use App\Services\HutangService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HutangApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HutangPiutang::query();

        if (in_array($request->query('jenis'), HutangService::JENIS, true)) {
            $query->where('jenis', $request->query('jenis'));
        }

        if (in_array($request->query('status'), ['belum', 'lunas'], true)) {
            $query->where('status', $request->query('status'));
        }

        $rows = $query
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => HutangResource::collection($rows)->resolve($request),
            'server_time' => waktu_api(),
        ]);
    }

    public function store(StoreHutangRequest $request, HutangService $service): JsonResponse
    {
        $hutang = $service->buat($request->validated());

        return response()->json([
            'data' => (new HutangResource($hutang))->resolve($request),
            'server_time' => waktu_api(),
        ], 201);
    }

    public function bayar(BayarHutangRequest $request, string $hutang, HutangService $service): JsonResponse
    {
        $catatan = HutangPiutang::query()->findOrFail((int) $hutang);
        [$catatan, $transaksi, $dibuat] = $service->bayar($catatan, $request->validated(), 'telegram');

        return response()->json([
            'data' => (new HutangResource($catatan))->resolve($request),
            'transaksi' => (new TransaksiResource($transaksi))->resolve($request),
            'dibuat' => $dibuat,
            'server_time' => waktu_api(),
        ], $dibuat ? 201 : 200);
    }

    public function destroy(Request $request, string $hutang, HutangService $service): JsonResponse
    {
        $catatan = HutangPiutang::withTrashed()->findOrFail((int) $hutang);
        $catatan = $service->hapus($catatan);

        return response()->json([
            'data' => (new HutangResource($catatan))->resolve($request),
            'dihapus' => true,
            'server_time' => waktu_api(),
        ]);
    }
}
