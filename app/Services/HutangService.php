<?php

namespace App\Services;

use App\Models\HutangPiutang;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\JumlahParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HutangService
{
    public const JENIS = ['hutang', 'piutang'];

    public function buat(array $data): HutangPiutang
    {
        $data = $this->normalisasi($data);

        return HutangPiutang::create([
            'pihak' => $data['pihak'],
            'jenis' => $data['jenis'],
            'nominal' => $data['nominal'],
            'sisa' => $data['nominal'],
            'tanggal' => $data['tanggal'],
            'jatuh_tempo' => $data['jatuh_tempo'],
            'status' => 'belum',
            'keterangan' => $data['keterangan'],
        ]);
    }

    /**
     * Record one payment/receipt and reduce the remaining amount.
     *
     * @return array{0: HutangPiutang, 1: Transaksi, 2: bool}
     */
    public function bayar(HutangPiutang $hutang, array $data, string $sumber = 'ui', ?User $user = null): array
    {
        return DB::transaction(function () use ($hutang, $data, $sumber, $user) {
            $terkunci = HutangPiutang::query()
                ->whereKey($hutang->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($terkunci->status === 'lunas' || $terkunci->sisa <= 0) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Catatan ini sudah lunas.',
                ]);
            }

            $jumlah = JumlahParser::parse($data['jumlah'] ?? null);
            if ($jumlah === null) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah bayar harus bilangan bulat positif.',
                ]);
            }

            $tanggal = isset($data['tanggal'])
                ? Carbon::parse((string) $data['tanggal'], 'Asia/Jakarta')->toDateString()
                : Carbon::today()->toDateString();

            $clientKey = 'HUTANG-'.$terkunci->id.'-'.Carbon::parse($tanggal)->format('Ymd').'-'.$jumlah;

            $lama = Transaksi::withTrashed()->where('client_key', $clientKey)->first();
            if ($lama !== null) {
                return [$terkunci, $lama, false];
            }

            if ($jumlah > $terkunci->sisa) {
                throw ValidationException::withMessages([
                    'jumlah' => 'Jumlah bayar melebihi sisa '.$terkunci->sisa.'.',
                ]);
            }

            $jenis = $terkunci->jenis === 'hutang' ? 'keluar' : 'masuk';
            $kategori = $terkunci->jenis === 'hutang' ? 'Hutang' : 'Piutang';

            $transaksi = app(TransaksiService::class)->buat([
                'tanggal' => $tanggal,
                'jenis' => $jenis,
                'kategori' => $kategori,
                'jumlah' => $jumlah,
                'keterangan' => $data['keterangan'] ?? 'Pembayaran '.$terkunci->pihak,
                'pencatat' => $user?->pencatat ?? ($data['pencatat'] ?? 'Tanpa nama'),
                'metode' => $data['metode'] ?? null,
                'client_key' => $clientKey,
            ], $sumber, $user);

            $sisaBaru = (int) $terkunci->sisa - $jumlah;
            $terkunci->forceFill([
                'sisa' => max(0, $sisaBaru),
                'status' => $sisaBaru <= 0 ? 'lunas' : 'belum',
            ])->save();

            return [$terkunci->refresh(), $transaksi, $transaksi->wasRecentlyCreated];
        });
    }

    public function hapus(HutangPiutang $hutang): HutangPiutang
    {
        if (! $hutang->trashed()) {
            $hutang->delete();
        }

        return $hutang->refresh();
    }

    /**
     * @return array{pihak: string, jenis: string, nominal: int, tanggal: string, jatuh_tempo: ?string, keterangan: ?string}
     */
    private function normalisasi(array $data): array
    {
        $pihak = preg_replace('/\s+/u', ' ', trim((string) ($data['pihak'] ?? ''))) ?? '';
        if ($pihak === '') {
            throw ValidationException::withMessages(['pihak' => 'Nama pihak wajib diisi.']);
        }
        if (mb_strlen($pihak) > 50) {
            throw ValidationException::withMessages(['pihak' => 'Nama pihak maksimal 50 karakter.']);
        }

        $jenis = mb_strtolower(trim((string) ($data['jenis'] ?? '')));
        if (! in_array($jenis, self::JENIS, true)) {
            throw ValidationException::withMessages(['jenis' => 'Jenis harus hutang atau piutang.']);
        }

        $nominal = JumlahParser::parse($data['nominal'] ?? null);
        if ($nominal === null) {
            throw ValidationException::withMessages(['nominal' => 'Nominal harus bilangan bulat positif.']);
        }

        $tanggal = isset($data['tanggal']) && $data['tanggal'] !== ''
            ? Carbon::parse((string) $data['tanggal'], 'Asia/Jakarta')->toDateString()
            : Carbon::today()->toDateString();

        $jatuhTempo = isset($data['jatuh_tempo']) && $data['jatuh_tempo'] !== ''
            ? Carbon::parse((string) $data['jatuh_tempo'], 'Asia/Jakarta')->toDateString()
            : null;

        $keterangan = trim((string) ($data['keterangan'] ?? ''));

        return [
            'pihak' => $pihak,
            'jenis' => $jenis,
            'nominal' => $nominal,
            'tanggal' => $tanggal,
            'jatuh_tempo' => $jatuhTempo,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ];
    }
}
