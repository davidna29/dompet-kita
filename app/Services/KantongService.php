<?php

namespace App\Services;

use App\Models\Kantong;
use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KantongService
{
    public const TIPE = ['harian', 'tabungan', 'investasi'];

    public function __construct(
        private readonly SaldoService $saldoService,
        private readonly TransaksiService $transaksiService,
    ) {}

    public function alasanArsip(Kantong $kantong): ?string
    {
        $saldo = $this->saldoService->hitung($kantong)['saldo'];

        if ($saldo !== 0) {
            return 'Pindahkan atau catat saldo sampai nol sebelum mengarsipkan kantong ini.';
        }

        $adaJadwalAktif = TransaksiBerulang::query()
            ->where('aktif', true)
            ->where(function ($query) use ($kantong) {
                $query->where('metode', $kantong->nama)
                    ->orWhere('kantong_tujuan', $kantong->nama);
            })
            ->exists();

        return $adaJadwalAktif
            ? 'Ubah atau nonaktifkan jadwal berulang yang memakai kantong ini terlebih dahulu.'
            : null;
    }

    public function gantiNama(Kantong $kantong, string $nama, ?User $user): void
    {
        $namaBaru = trim($nama);

        DB::transaction(function () use ($kantong, $namaBaru, $user) {
            $terkunci = Kantong::query()->whereKey($kantong->getKey())->lockForUpdate()->firstOrFail();
            $namaLama = $terkunci->nama;

            if ($namaLama === $namaBaru) {
                return;
            }

            $namaSudahDipakai = Kantong::withTrashed()
                ->where('nama', $namaBaru)
                ->where('id', '!=', $terkunci->getKey())
                ->exists();

            if ($namaSudahDipakai) {
                throw ValidationException::withMessages([
                    'nama' => 'Nama kantong sudah digunakan, termasuk oleh kantong yang diarsipkan.',
                ]);
            }

            $mutasiBertabrakan = Transaksi::withTrashed()
                ->where('kategori', 'Mutasi')
                ->where(function ($query) use ($namaLama, $namaBaru) {
                    $query->where(function ($query) use ($namaLama, $namaBaru) {
                        $query->where('metode', $namaLama)
                            ->where('kantong_tujuan', $namaBaru);
                    })->orWhere(function ($query) use ($namaLama, $namaBaru) {
                        $query->where('metode', $namaBaru)
                            ->where('kantong_tujuan', $namaLama);
                    });
                })
                ->exists();

            if ($mutasiBertabrakan) {
                throw ValidationException::withMessages([
                    'nama' => 'Nama baru akan membuat kantong asal dan tujuan sama pada transaksi Mutasi. Koreksi Mutasi tersebut terlebih dahulu.',
                ]);
            }

            Transaksi::withTrashed()
                ->where(function ($query) use ($namaLama) {
                    $query->where('metode', $namaLama)
                        ->orWhere('kantong_tujuan', $namaLama);
                })
                ->orderBy('id')
                ->chunkById(100, function ($transaksi) use ($namaLama, $namaBaru, $user) {
                    foreach ($transaksi as $baris) {
                        $perubahan = [];

                        if ($baris->metode === $namaLama) {
                            $perubahan['metode'] = $namaBaru;
                        }

                        if ($baris->kantong_tujuan === $namaLama) {
                            $perubahan['kantong_tujuan'] = $namaBaru;
                        }

                        if ($perubahan !== []) {
                            $this->transaksiService->perbarui($baris, $perubahan, $user, 'ui');
                        }
                    }
                });

            TransaksiBerulang::withTrashed()
                ->where(function ($query) use ($namaLama) {
                    $query->where('metode', $namaLama)
                        ->orWhere('kantong_tujuan', $namaLama);
                })
                ->orderBy('id')
                ->chunkById(100, function ($jadwal) use ($namaLama, $namaBaru) {
                    foreach ($jadwal as $baris) {
                        $perubahan = [];

                        if ($baris->metode === $namaLama) {
                            $perubahan['metode'] = $namaBaru;
                        }

                        if ($baris->kantong_tujuan === $namaLama) {
                            $perubahan['kantong_tujuan'] = $namaBaru;
                        }

                        if ($perubahan !== []) {
                            $baris->update($perubahan);
                        }
                    }
                });

            $terkunci->update(['nama' => $namaBaru]);
        });
    }

    public function ubahTipe(Kantong $kantong, string $tipe): void
    {
        if (! in_array($tipe, self::TIPE, true)) {
            throw ValidationException::withMessages([
                'tipe' => 'Tipe kantong tidak valid.',
            ]);
        }

        if ($kantong->tipe === $tipe) {
            return;
        }

        $kantong->forceFill(['tipe' => $tipe])->save();
    }

    public function arsipkan(Kantong $kantong): void
    {
        DB::transaction(function () use ($kantong) {
            $terkunci = Kantong::query()->whereKey($kantong->getKey())->lockForUpdate()->firstOrFail();
            $alasan = $this->alasanArsip($terkunci);

            if ($alasan !== null) {
                throw ValidationException::withMessages(['kantong' => $alasan]);
            }

            $terkunci->delete();
        });
    }
}
