<?php

namespace App\Services;

use App\Models\Anggaran;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AnggaranService
{
    public function untukPeriode(string $periode): array
    {
        $this->pastikanPeriode($periode);

        $rows = Anggaran::query()->where('periode', $periode)->get();
        $dipinjam = false;
        $periodeSebelumnya = null;

        if ($rows->isEmpty()) {
            $sebelum = Anggaran::query()
                ->where('periode', '<', $periode)
                ->orderByDesc('periode')
                ->first();

            if ($sebelum !== null) {
                $periodeSebelumnya = $sebelum->periode;
                $rows = Anggaran::query()->where('periode', $sebelum->periode)->get();
                $dipinjam = true;
            }
        }

        $ringkasan = app(RingkasanService::class)->ringkasan(
            Carbon::parse($periode.'-01')->startOfMonth(),
            Carbon::parse($periode.'-01')->endOfMonth()
        );

        $belanjaPerKategori = collect($ringkasan['per_kategori'])->keyBy('kategori');

        $totalRow = $rows->first(fn (Anggaran $row) => $row->kategori === null || $row->kategori === '');
        $totalAnggaran = $totalRow?->jumlah ?? (int) $rows->filter(fn (Anggaran $row) => $row->kategori !== null && $row->kategori !== '')->sum('jumlah');

        $detail = $rows
            ->filter(fn (Anggaran $row) => $row->kategori !== null && $row->kategori !== '')
            ->map(function (Anggaran $row) use ($belanjaPerKategori, $dipinjam) {
                $terpakai = (int) ($belanjaPerKategori[$row->kategori]['total'] ?? 0);

                return $this->baris($row->kategori, (int) $row->jumlah, $terpakai, (int) $row->ambang, $dipinjam ? null : $row->id);
            })
            ->sortByDesc('persen')
            ->values();

        $totalTerpakai = (int) $ringkasan['keluar'];
        $totalBaris = $this->baris(
            'Total bulanan',
            $totalAnggaran,
            $totalTerpakai,
            (int) ($totalRow?->ambang ?? 80),
            $dipinjam ? null : $totalRow?->id
        );

        return [
            'periode' => $periode,
            'rows' => $detail,
            'total' => $totalBaris,
            'punya_total_row' => $totalRow !== null,
            'dipinjam' => $dipinjam,
            'periode_sebelumnya' => $periodeSebelumnya,
            'total_anggaran' => $totalAnggaran,
            'total_terpakai' => $totalTerpakai,
        ];
    }

    public function simpan(string $periode, ?string $kategori, int $jumlah, int $ambang = 80): Anggaran
    {
        $this->pastikanPeriode($periode);

        if ($jumlah <= 0) {
            throw ValidationException::withMessages([
                'jumlah' => 'Jumlah anggaran harus lebih dari nol.',
            ]);
        }

        $kategori = $this->normalisasiKategori($kategori);

        $row = Anggaran::withTrashed()->firstOrNew([
            'periode' => $periode,
            'kategori' => $kategori,
        ]);

        $row->fill([
            'jumlah' => $jumlah,
            'ambang' => max(1, min(100, $ambang)),
        ]);
        $row->deleted_at = null;
        $row->save();

        return $row;
    }

    public function hapus(Anggaran $anggaran): void
    {
        $anggaran->delete();
    }

    public function salinDariBulanLalu(string $periode): int
    {
        $this->pastikanPeriode($periode);

        if (Anggaran::withTrashed()->where('periode', $periode)->exists()) {
            return 0;
        }

        $sebelum = Anggaran::query()
            ->where('periode', '<', $periode)
            ->orderByDesc('periode')
            ->first();

        if ($sebelum === null) {
            return 0;
        }

        $rows = Anggaran::query()->where('periode', $sebelum->periode)->get();
        $jumlah = 0;

        foreach ($rows as $row) {
            Anggaran::create([
                'periode' => $periode,
                'kategori' => $row->kategori,
                'jumlah' => $row->jumlah,
                'ambang' => $row->ambang,
            ]);
            $jumlah++;
        }

        return $jumlah;
    }

    public function peringatan(string $periode): Collection
    {
        return $this->untukPeriode($periode)['rows']
            ->filter(fn (array $row) => $row['status'] !== 'aman')
            ->values();
    }

    private function baris(string $kategori, int $anggaran, int $terpakai, int $ambang, ?int $id = null): array
    {
        $persen = $anggaran > 0 ? (int) round(($terpakai / $anggaran) * 100) : 0;

        return [
            'id' => $id,
            'kategori' => $kategori,
            'anggaran' => $anggaran,
            'terpakai' => $terpakai,
            'sisa' => $anggaran - $terpakai,
            'persen' => $persen,
            'ambang' => $ambang,
            'status' => $persen >= 100 ? 'lewat' : ($persen >= $ambang ? 'hampir' : 'aman'),
        ];
    }

    private function normalisasiKategori(?string $kategori): ?string
    {
        $kategori = preg_replace('/\s+/u', ' ', trim((string) $kategori)) ?? '';

        if ($kategori === '' || mb_strtolower($kategori) === '__total__') {
            return null;
        }

        $kategori = mb_convert_case($kategori, MB_CASE_TITLE, 'UTF-8');

        if (mb_strtolower($kategori) === 'mutasi') {
            throw ValidationException::withMessages([
                'kategori' => 'Mutasi bukan belanja; anggaran Mutasi tidak dihitung.',
            ]);
        }

        if (mb_strlen($kategori) > 50) {
            throw ValidationException::withMessages([
                'kategori' => 'Kategori maksimal 50 karakter.',
            ]);
        }

        return $kategori;
    }

    private function pastikanPeriode(string $periode): void
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $periode)) {
            abort(404);
        }
    }
}
