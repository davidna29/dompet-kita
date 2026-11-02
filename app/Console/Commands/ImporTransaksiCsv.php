<?php

namespace App\Console\Commands;

use App\Models\Transaksi;
use App\Services\TransaksiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImporTransaksiCsv extends Command
{
    protected $signature = 'transaksi:impor-csv {path : Lokasi file CSV} {--dry-run : Validasi tanpa menulis}';

    protected $description = 'Impor baris transaksi dari CSV cache STB (header 8 kolom)';

    public function handle(TransaksiService $service): int
    {
        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $file = fopen($path, 'rb');
        if ($file === false) {
            $this->error('File tidak bisa dibuka.');

            return self::FAILURE;
        }

        $header = fgetcsv($file);
        if ($header === false) {
            fclose($file);
            $this->error('CSV kosong.');

            return self::FAILURE;
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $wajib = ['tanggal', 'jenis', 'kategori', 'jumlah', 'keterangan', 'pencatat', 'metode', 'kantong_tujuan'];

        $header = array_map(fn ($nilai) => trim((string) $nilai), $header);
        if (count($header) === 7) {
            $header[] = 'kantong_tujuan';
        }
        if ($header !== $wajib) {
            fclose($file);
            $this->error('Header harus: '.implode(',', $wajib));

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $baris = 0;
        $dibuat = 0;
        $dilewati = 0;
        $gagal = [];

        DB::beginTransaction();

        while (($data = fgetcsv($file)) !== false) {
            if ($data === [null] || count(array_filter($data, fn ($nilai) => trim((string) $nilai) !== '')) === 0) {
                continue;
            }

            $baris++;
            $data = array_pad($data, 8, '');

            $row = array_combine($wajib, array_map(fn ($nilai) => trim((string) $nilai), array_slice($data, 0, 8)));

            if (Transaksi::withTrashed()->where('client_key', 'MIGRASI-'.$baris)->exists()) {
                $dilewati++;

                continue;
            }

            try {
                if (! $dryRun) {
                    $service->buat([
                        'tanggal' => $row['tanggal'],
                        'jenis' => $row['jenis'],
                        'kategori' => $row['kategori'],
                        'jumlah' => $row['jumlah'],
                        'keterangan' => $row['keterangan'],
                        'pencatat' => $row['pencatat'],
                        'metode' => $row['metode'],
                        'kantong_tujuan' => $row['kantong_tujuan'],
                        'sumber' => 'telegram',
                        'client_key' => 'MIGRASI-'.$baris,
                    ], 'telegram');
                }
                $dibuat++;
            } catch (Throwable $e) {
                $gagal[] = "Baris {$baris}: {$e->getMessage()}";
            }
        }

        fclose($file);

        if ($dryRun || $gagal !== []) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $this->table(
            ['Baris', 'Dibuat', 'Dilewati', 'Gagal'],
            [[$baris, $dibuat, $dilewati, count($gagal)]]
        );

        if ($gagal !== []) {
            $this->error('Impor dibatalkan; tidak ada baris yang ditulis.');
            foreach ($gagal as $pesan) {
                $this->line('  · '.$pesan);
            }

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Dry-run selesai. Tidak ada perubahan.');

            return self::SUCCESS;
        }

        $this->info('Impor selesai. Jalankan GET /api/transaksi untuk memverifikasi dari STB.');

        return self::SUCCESS;
    }
}
