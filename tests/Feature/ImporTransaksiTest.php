<?php

namespace Tests\Feature;

use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImporTransaksiTest extends TestCase
{
    use RefreshDatabase;

    public function test_impor_csv_idempoten(): void
    {
        $path = storage_path('app/test-import.csv');
        File::ensureDirectoryExists(dirname($path));

        File::put($path, implode("\n", [
            'tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan',
            '2026-10-01,keluar,Makanan,35000,Belanja pasar,David NA,Tunai,',
            '2026-10-02,keluar,Mutasi,100000,Tarik tunai,David NA,BSI,Tunai',
        ]));

        $this->artisan('transaksi:impor-csv', ['path' => $path])->assertSuccessful();

        $this->assertDatabaseCount('transaksi', 2);
        $this->assertDatabaseHas('transaksi', ['client_key' => 'MIGRASI-1', 'sumber' => 'telegram', 'jumlah' => 35000]);
        $this->assertDatabaseHas('transaksi', ['client_key' => 'MIGRASI-2', 'kantong_tujuan' => 'Tunai']);

        $this->artisan('transaksi:impor-csv', ['path' => $path])->assertSuccessful();
        $this->assertDatabaseCount('transaksi', 2);

        File::delete($path);
    }

    public function test_dry_run_tidak_menulis(): void
    {
        $path = storage_path('app/test-import-dry.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan\n2026-10-01,keluar,Makanan,35000,Belanja,David NA,Tunai,");

        $this->artisan('transaksi:impor-csv', ['path' => $path, '--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('transaksi', 0);
        File::delete($path);
    }

    public function test_header_salah_ditolak(): void
    {
        $path = storage_path('app/test-import-wrong.csv');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "tanggal,jenis,kategori,jumlah\n2026-10-01,keluar,Makanan,35000");

        $this->artisan('transaksi:impor-csv', ['path' => $path])->assertFailed();

        File::delete($path);
    }
}
