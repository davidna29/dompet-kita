<?php

namespace App\Services;

use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Models\User;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PengingatService
{
    public function daftar(): Collection
    {
        return TransaksiBerulang::query()
            ->aktif()
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
}
