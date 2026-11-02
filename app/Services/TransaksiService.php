<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\TransaksiRiwayat;
use App\Models\User;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransaksiService
{
    public function buat(array $data, string $sumber = 'ui', ?User $user = null): Transaksi
    {
        $data = $this->normalisasi($data, $sumber, $user);

        if (! empty($data['client_key'])) {
            $lama = Transaksi::withTrashed()->where('client_key', $data['client_key'])->first();
            if ($lama !== null) {
                return $lama;
            }
        }

        try {
            return DB::transaction(function () use ($data) {
                $transaksi = Transaksi::create($data);

                $this->catatRiwayat(
                    $transaksi,
                    'dibuat',
                    $data['pencatat'] ?? null,
                    $data['sumber'],
                    null,
                    $this->snapshot($transaksi)
                );

                return $transaksi;
            });
        } catch (UniqueConstraintViolationException $e) {
            if (! empty($data['client_key'])) {
                $lama = Transaksi::withTrashed()->where('client_key', $data['client_key'])->first();
                if ($lama !== null) {
                    return $lama;
                }
            }

            throw $e;
        }
    }

    public function perbarui(Transaksi $transaksi, array $data, ?User $user = null, string $sumber = 'ui'): Transaksi
    {
        $awal = $this->snapshot($transaksi);

        $gabungan = array_merge(
            $transaksi->only([
                'tanggal', 'jenis', 'kategori', 'jumlah', 'keterangan', 'pencatat',
                'metode', 'sumber', 'client_key', 'kantong_tujuan',
            ]),
            $data
        );

        $baru = $this->normalisasi($gabungan, $transaksi->sumber, null);

        // The source records where the row came from; editing does not rewrite it.
        $baru['sumber'] = $transaksi->sumber;
        $baru['client_key'] = $transaksi->client_key;

        return DB::transaction(function () use ($transaksi, $baru, $awal, $user, $sumber) {
            $transaksi->fill($baru);

            if ($transaksi->isDirty()) {
                $transaksi->save();

                $this->catatRiwayat(
                    $transaksi,
                    'diubah',
                    $user?->pencatat ?? 'Hermes',
                    $sumber,
                    $awal,
                    $this->snapshot($transaksi)
                );
            }

            return $transaksi->refresh();
        });
    }

    public function hapus(Transaksi $transaksi, ?User $user = null, string $sumber = 'ui'): Transaksi
    {
        if ($transaksi->trashed()) {
            return $transaksi;
        }

        $sebelum = $this->snapshot($transaksi);

        return DB::transaction(function () use ($transaksi, $sebelum, $user, $sumber) {
            $transaksi->delete();

            $this->catatRiwayat(
                $transaksi,
                'dihapus',
                $user?->pencatat ?? 'Hermes',
                $sumber,
                $sebelum,
                null
            );

            return $transaksi->refresh();
        });
    }

    public function normalisasi(array $data, string $sumber = 'ui', ?User $user = null): array
    {
        $jenis = mb_strtolower(trim((string) ($data['jenis'] ?? '')));
        if (! in_array($jenis, ['masuk', 'keluar'], true)) {
            throw ValidationException::withMessages(['jenis' => 'Jenis transaksi harus masuk atau keluar.']);
        }

        $kategori = preg_replace('/\s+/u', ' ', trim((string) ($data['kategori'] ?? ''))) ?? '';
        $kategori = mb_convert_case($kategori, MB_CASE_TITLE, 'UTF-8');
        if ($kategori === '') {
            throw ValidationException::withMessages(['kategori' => 'Kategori wajib diisi.']);
        }
        if (mb_strtolower($kategori) === 'mutasi') {
            $kategori = 'Mutasi';
        }
        if ($kategori === 'Mutasi' && $jenis !== 'keluar') {
            throw ValidationException::withMessages(['jenis' => 'Mutasi selalu berjenis keluar.']);
        }
        if (mb_strlen($kategori) > 50) {
            throw ValidationException::withMessages(['kategori' => 'Kategori maksimal 50 karakter.']);
        }

        $jumlah = JumlahParser::parse($data['jumlah'] ?? null);
        if ($jumlah === null) {
            throw ValidationException::withMessages(['jumlah' => 'Jumlah harus bilangan bulat positif. Contoh: 35.000 atau 35rb.']);
        }

        $tanggal = $data['tanggal'] ?? null;
        if ($tanggal instanceof Carbon) {
            $tanggal = $tanggal->format('Y-m-d');
        }
        try {
            $tanggal = Carbon::parse((string) $tanggal, 'Asia/Jakarta')->toDateString();
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['tanggal' => 'Tanggal tidak valid.']);
        }

        $metode = trim((string) ($data['metode'] ?? '')) ?: null;
        $tujuan = trim((string) ($data['kantong_tujuan'] ?? '')) ?: null;

        if ($kategori === 'Mutasi') {
            if ($metode === null || $tujuan === null) {
                throw ValidationException::withMessages(['kantong_tujuan' => 'Mutasi wajib memilih kantong asal dan kantong tujuan.']);
            }
            if ($metode === $tujuan) {
                throw ValidationException::withMessages(['kantong_tujuan' => 'Kantong asal dan tujuan harus berbeda.']);
            }
        } else {
            $tujuan = null;
        }

        $pencatat = $user?->pencatat;
        if ($pencatat === null || $pencatat === '') {
            $pencatat = trim((string) ($data['pencatat'] ?? '')) ?: 'Tanpa nama';
        }

        $sumber = in_array($sumber, ['ui', 'telegram'], true) ? $sumber : 'ui';

        $keterangan = trim((string) ($data['keterangan'] ?? ''));
        $clientKey = trim((string) ($data['client_key'] ?? ''));

        if ($clientKey !== '' && mb_strlen($clientKey) > 64) {
            throw ValidationException::withMessages(['client_key' => 'client_key maksimal 64 karakter.']);
        }

        return [
            'tanggal' => $tanggal,
            'jenis' => $jenis,
            'kategori' => $kategori,
            'jumlah' => $jumlah,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
            'pencatat' => $pencatat,
            'metode' => $metode,
            'sumber' => $sumber,
            'client_key' => $clientKey !== '' ? $clientKey : null,
            'kantong_tujuan' => $tujuan,
        ];
    }

    public function snapshot(Transaksi $transaksi): array
    {
        return [
            'id' => $transaksi->id,
            'tanggal' => $transaksi->tanggal?->format('Y-m-d'),
            'jenis' => $transaksi->jenis,
            'kategori' => $transaksi->kategori,
            'jumlah' => (int) $transaksi->jumlah,
            'keterangan' => $transaksi->keterangan,
            'pencatat' => $transaksi->pencatat,
            'metode' => $transaksi->metode,
            'sumber' => $transaksi->sumber,
            'client_key' => $transaksi->client_key,
            'kantong_tujuan' => $transaksi->kantong_tujuan,
            'created_at' => $transaksi->created_at?->format('Y-m-d\TH:i:s'),
            'updated_at' => $transaksi->updated_at?->format('Y-m-d\TH:i:s'),
            'deleted_at' => $transaksi->deleted_at?->format('Y-m-d\TH:i:s'),
        ];
    }

    private function catatRiwayat(
        Transaksi $transaksi,
        string $aksi,
        ?string $oleh,
        string $sumber,
        ?array $sebelum,
        ?array $sesudah
    ): void {
        TransaksiRiwayat::create([
            'transaksi_id' => $transaksi->id,
            'aksi' => $aksi,
            'oleh' => $oleh,
            'sumber' => $sumber,
            'sebelum' => $sebelum,
            'sesudah' => $sesudah,
            'created_at' => now(),
        ]);
    }
}
