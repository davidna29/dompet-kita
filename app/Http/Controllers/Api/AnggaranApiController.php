<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SimpanAnggaranRequest;
use App\Models\Anggaran;
use App\Services\AnggaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnggaranApiController extends Controller
{
    public function index(Request $request, AnggaranService $service): JsonResponse
    {
        $data = $request->validate([
            'periode' => ['nullable', 'date_format:Y-m'],
        ], [
            'periode.date_format' => 'Periode harus berformat YYYY-MM.',
        ]);

        $periode = $data['periode'] ?? now()->format('Y-m');

        return $this->response($service->untukPeriode($periode));
    }

    public function store(SimpanAnggaranRequest $request, AnggaranService $service): JsonResponse
    {
        $data = $request->validated();

        $anggaran = $service->simpan(
            $data['periode'],
            $data['kategori'] ?? null,
            (int) $data['jumlah'],
            (int) ($data['ambang'] ?? 80)
        );

        $hasil = $service->untukPeriode($data['periode']);

        return $this->response($hasil, [
            'row' => $this->baris($hasil, $anggaran),
            'dibuat' => $anggaran->wasRecentlyCreated,
        ], $anggaran->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(string $anggaran, AnggaranService $service): JsonResponse
    {
        $row = Anggaran::withTrashed()->findOrFail((int) $anggaran);

        if (! $row->trashed()) {
            $service->hapus($row);
        }

        return $this->response($service->untukPeriode($row->periode), [
            'dihapus' => true,
        ]);
    }

    public function salin(Request $request, AnggaranService $service): JsonResponse
    {
        $data = $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ], [
            'periode.required' => 'Periode wajib diisi.',
            'periode.date_format' => 'Periode harus berformat YYYY-MM.',
        ]);

        $jumlah = $service->salinDariBulanLalu($data['periode']);

        return $this->response($service->untukPeriode($data['periode']), [
            'jumlah_disalin' => $jumlah,
            'dibuat' => $jumlah > 0,
        ]);
    }

    private function response(array $hasil, array $extra = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $this->snapshot($hasil),
            ...$extra,
            'server_time' => waktu_api(),
        ], $status);
    }

    private function snapshot(array $hasil): array
    {
        return [
            'periode' => $hasil['periode'],
            'dipinjam' => $hasil['dipinjam'],
            'periode_sebelumnya' => $hasil['periode_sebelumnya'],
            'total_anggaran' => $hasil['total_anggaran'],
            'total_terpakai' => $hasil['total_terpakai'],
            'total' => $hasil['total'],
            'rows' => $hasil['rows']->values(),
        ];
    }

    private function baris(array $hasil, Anggaran $anggaran): ?array
    {
        if ($anggaran->kategori === null) {
            return $hasil['total'];
        }

        return $hasil['rows']->firstWhere('kategori', $anggaran->kategori);
    }
}
