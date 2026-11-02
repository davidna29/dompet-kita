<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnggaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnggaranApiController extends Controller
{
    public function index(Request $request, AnggaranService $service): JsonResponse
    {
        $data = $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
        ]);

        $periode = $data['periode'] ?? now()->format('Y-m');
        $hasil = $service->untukPeriode($periode);

        return response()->json([
            'data' => [
                'periode' => $hasil['periode'],
                'dipinjam' => $hasil['dipinjam'],
                'periode_sebelumnya' => $hasil['periode_sebelumnya'],
                'total_anggaran' => $hasil['total_anggaran'],
                'total_terpakai' => $hasil['total_terpakai'],
                'total' => $hasil['total'],
                'rows' => $hasil['rows']->values(),
            ],
            'server_time' => waktu_api(),
        ]);
    }
}
