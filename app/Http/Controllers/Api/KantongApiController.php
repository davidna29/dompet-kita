<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SaldoService;
use Illuminate\Http\JsonResponse;

class KantongApiController extends Controller
{
    public function index(SaldoService $service): JsonResponse
    {
        return response()->json([
            'data' => $service->semua()->values(),
            'server_time' => waktu_api(),
        ]);
    }
}
