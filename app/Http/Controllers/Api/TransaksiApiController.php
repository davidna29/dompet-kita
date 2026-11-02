<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListTransaksiRequest;
use App\Http\Requests\Api\StoreTransaksiRequest;
use App\Http\Requests\Api\UpdateTransaksiRequest;
use App\Http\Resources\TransaksiResource;
use App\Models\Transaksi;
use App\Services\TransaksiService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransaksiApiController extends Controller
{
    public function index(ListTransaksiRequest $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 100);
        $offset = (int) $request->integer('offset', 0);

        $query = Transaksi::query()->withTrashed();

        if ($request->filled('since')) {
            $since = Carbon::createFromFormat('Y-m-d\TH:i:s', $request->string('since')->toString(), 'Asia/Jakarta');
            $query->where('updated_at', '>=', $since->format('Y-m-d H:i:s'));
        }

        $rows = $query
            ->orderBy('updated_at')
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit + 1)
            ->get();

        $adaLagi = $rows->count() > $limit;
        $rows = $rows->take($limit)->values();

        return response()->json([
            'data' => TransaksiResource::collection($rows)->resolve($request),
            'next_offset' => $adaLagi ? $offset + $limit : null,
            'server_time' => waktu_api(),
        ]);
    }

    public function store(StoreTransaksiRequest $request, TransaksiService $service): JsonResponse
    {
        $transaksi = $service->buat($request->validated(), 'telegram');

        $status = $transaksi->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'data' => (new TransaksiResource($transaksi))->resolve($request),
            'server_time' => waktu_api(),
        ], $status);
    }

    public function update(UpdateTransaksiRequest $request, Transaksi $transaksi, TransaksiService $service): JsonResponse
    {
        $transaksi = $service->perbarui($transaksi, $request->validated(), null, 'telegram');

        return response()->json([
            'data' => (new TransaksiResource($transaksi))->resolve($request),
            'server_time' => waktu_api(),
        ]);
    }

    public function destroy(Request $request, int $transaksi, TransaksiService $service): JsonResponse
    {
        $transaksi = Transaksi::withTrashed()->findOrFail($transaksi);
        $transaksi = $service->hapus($transaksi, null, 'telegram');

        return response()->json([
            'data' => (new TransaksiResource($transaksi))->resolve($request),
            'server_time' => waktu_api(),
        ]);
    }
}
