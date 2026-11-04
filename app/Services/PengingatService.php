<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Models\User;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PengingatService
{
    public function daftar(bool $termasukNonaktif = false): Collection
    {
        $query = TransaksiBerulang::query();

        if (! $termasukNonaktif) {
            $query->aktif();
        }

        return $query
            ->orderBy('jatuh_tempo')
            ->orderBy('nama')
            ->get();
    }

    public function jatuhTempo(int $hariKeDepan = 7): Collection
    {
        return TransaksiBerulang::query()
            ->aktif()
            ->where('jatuh_tempo', '<=', Carbon::today()->addDays($hariKeDepan)->toDateString())
            ->orderBy('jatuh_tempo')
            ->get();
    }

    public function simpan(array $data): TransaksiBerulang
    {
        $data = $this->normalisasi($data);

        if ($data['client_key'] !== null) {
            $lama = TransaksiBerulang::withTrashed()
                ->where('client_key', $data['client_key'])
                ->first();

            if ($lama !== null) {
                return $lama;
            }
        }

        try {
            return TransaksiBerulang::create($data);
        } catch (UniqueConstraintViolationException $e) {
            if ($data['client_key'] !== null) {
                $lama = TransaksiBerulang::withTrashed()
                    ->where('client_key', $data['client_key'])
                    ->first();

                if ($lama !== null) {
                    return $lama;
                }
            }

            throw $e;
        }
    }

    public function perbarui(TransaksiBerulang $jadwal, array $data): TransaksiBerulang
    {
        $gabungan = array_merge(
            $jadwal->only([
                'nama', 'kategori', 'jumlah', 'aturan', 'jatuh_tempo',
                'metode', 'kantong_tujuan', 'pencatat', 'aktif',
            ]),
            $data
        );

        $baru = $this->normalisasi($gabungan, $jadwal);
        $baru['client_key'] = $jadwal->client_key;

        $jadwal->fill($baru);

        if ($jadwal->isDirty()) {
            $jadwal->save();
        }

        return $jadwal->refresh();
    }

    public function hapus(TransaksiBerulang $jadwal): TransaksiBerulang
    {
        if (! $jadwal->trashed()) {
            $jadwal->delete();
        }

        return $jadwal->refresh();
    }

    /**
     * Confirm one due occurrence. Idempotent through ULANG-<id>-<YYYYMMDD>.
     *
     * @return array{0: Transaksi, 1: bool}
     */
    public function konfirmasi(TransaksiBerulang $jadwal, array $data = [], string $sumber = 'ui', ?User $user = null): array
    {
        if (! $jadwal->aktif) {
            throw ValidationException::withMessages(['jatuh_tempo' => 'Jadwal ini tidak aktif.']);
        }

        $jatuhTempo = $jadwal->jatuh_tempo->format('Y-m-d');
        $occurrence = isset($data['jatuh_tempo'])
            ? Carbon::parse((string) $data['jatuh_tempo'])->toDateString()
            : $jatuhTempo;

        $clientKey = 'ULANG-'.$jadwal->id.'-'.Carbon::parse($occurrence)->format('Ymd');
        $lama = Transaksi::withTrashed()->where('client_key', $clientKey)->first();
        if ($lama !== null) {
            return [$lama, false];
        }

        if ($occurrence !== $jatuhTempo) {
            throw ValidationException::withMessages([
                'jatuh_tempo' => 'Jatuh tempo ini sudah bergeser. Muat ulang jadwal sebelum mengonfirmasi.',
            ]);
        }

        $jumlah = array_key_exists('jumlah', $data)
            ? JumlahParser::parse($data['jumlah'])
            : $jadwal->jumlah;

        if ($jumlah === null) {
            throw ValidationException::withMessages(['jumlah' => 'Jumlah konfirmasi harus bilangan bulat positif.']);
        }

        $transaksi = app(TransaksiService::class)->buat([
            'tanggal' => $data['tanggal'] ?? $occurrence,
            'jenis' => 'keluar',
            'kategori' => $jadwal->kategori,
            'jumlah' => $jumlah,
            'keterangan' => $data['keterangan'] ?? $jadwal->nama,
            'pencatat' => $jadwal->pencatat,
            'metode' => $jadwal->metode,
            'kantong_tujuan' => $jadwal->kantong_tujuan,
            'client_key' => $clientKey,
        ], $sumber, null);

        if ($transaksi->wasRecentlyCreated) {
            $jadwal->majukanJatuhTempo();
        }

        return [$transaksi, $transaksi->wasRecentlyCreated];
    }

    private function normalisasi(array $data, ?TransaksiBerulang $jadwal = null): array
    {
        $nama = trim((string) ($data['nama'] ?? ''));

        if ($nama === '') {
            throw ValidationException::withMessages(['nama' => 'Nama tagihan wajib diisi.']);
        }

        if (mb_strlen($nama) > 80) {
            throw ValidationException::withMessages(['nama' => 'Nama tagihan maksimal 80 karakter.']);
        }

        $kategori = preg_replace('/\s+/u', ' ', trim((string) ($data['kategori'] ?? ''))) ?? '';
        $kategori = mb_convert_case($kategori, MB_CASE_TITLE, 'UTF-8');

        if ($kategori === '') {
            throw ValidationException::withMessages(['kategori' => 'Kategori wajib diisi.']);
        }

        if (mb_strtolower($kategori) === 'mutasi') {
            $kategori = 'Mutasi';
        }

        if (mb_strlen($kategori) > 50) {
            throw ValidationException::withMessages(['kategori' => 'Kategori maksimal 50 karakter.']);
        }

        $aturan = mb_strtolower(trim((string) ($data['aturan'] ?? '')));
        if (! in_array($aturan, ['bulanan', 'mingguan', 'tahunan'], true)) {
            throw ValidationException::withMessages(['aturan' => 'Aturan ulang tidak valid.']);
        }

        $jumlah = JumlahParser::parse($data['jumlah'] ?? null);
        if ($jumlah === null) {
            throw ValidationException::withMessages(['jumlah' => 'Jumlah harus bilangan bulat positif.']);
        }

        try {
            $jatuhTempo = Carbon::parse((string) ($data['jatuh_tempo'] ?? ''), 'Asia/Jakarta')->toDateString();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['jatuh_tempo' => 'Jatuh tempo tidak valid.']);
        }

        $metode = trim((string) ($data['metode'] ?? '')) ?: null;
        $tujuan = trim((string) ($data['kantong_tujuan'] ?? '')) ?: null;

        if ($kategori === 'Mutasi') {
            if ($metode === null || $tujuan === null) {
                throw ValidationException::withMessages([
                    'kantong_tujuan' => 'Mutasi membutuhkan kantong asal dan tujuan yang berbeda.',
                ]);
            }

            if ($metode === $tujuan) {
                throw ValidationException::withMessages([
                    'kantong_tujuan' => 'Mutasi membutuhkan kantong asal dan tujuan yang berbeda.',
                ]);
            }
        } else {
            $tujuan = null;
        }

        $pencatat = trim((string) ($data['pencatat'] ?? ''));
        if ($pencatat === '') {
            throw ValidationException::withMessages(['pencatat' => 'Pencatat wajib diisi.']);
        }

        if (mb_strlen($pencatat) > 50) {
            throw ValidationException::withMessages(['pencatat' => 'Pencatat maksimal 50 karakter.']);
        }

        $aktif = array_key_exists('aktif', $data)
            ? (bool) $data['aktif']
            : ($jadwal?->aktif ?? true);

        $clientKey = trim((string) ($data['client_key'] ?? '')) ?: null;
        if ($clientKey !== null && mb_strlen($clientKey) > 64) {
            throw ValidationException::withMessages(['client_key' => 'client_key maksimal 64 karakter.']);
        }

        return [
            'nama' => $nama,
            'kategori' => $kategori,
            'jumlah' => $jumlah,
            'aturan' => $aturan,
            'jatuh_tempo' => $jatuhTempo,
            'metode' => $metode,
            'kantong_tujuan' => $tujuan,
            'pencatat' => $pencatat,
            'aktif' => $aktif,
            'client_key' => $clientKey,
        ];
    }
}
