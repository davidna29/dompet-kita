<?php

namespace Tests\Feature;

use App\Models\Kantong;
use App\Models\User;
use App\Services\RingkasanService;
use App\Services\SaldoService;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KantongTipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_kantong_default_tipe_harian(): void
    {
        $kantong = Kantong::create([
            'nama' => 'Tunai',
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => today()->toDateString(),
            'urutan' => 1,
        ]);

        $this->assertSame('harian', $kantong->refresh()->tipe);
    }

    public function test_store_menyimpan_tipe_dan_menolak_tipe_tidak_valid(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);

        $this->actingAs($user)
            ->post(route('kantong.store'), [
                'nama' => 'Reksa Dana',
                'tipe' => 'investasi',
                'saldo_awal' => '1.000.000',
                'tanggal_saldo_awal' => today()->toDateString(),
            ])
            ->assertRedirect(route('kantong'));

        $this->assertDatabaseHas('kantong', [
            'nama' => 'Reksa Dana',
            'tipe' => 'investasi',
        ]);

        $this->actingAs($user)
            ->post(route('kantong.store'), [
                'nama' => 'Salah',
                'tipe' => 'deposito',
                'saldo_awal' => '0',
                'tanggal_saldo_awal' => today()->toDateString(),
            ])
            ->assertSessionHasErrors('tipe');

        $this->assertDatabaseMissing('kantong', ['nama' => 'Salah']);
    }

    public function test_total_by_tipe_menjumlahkan_saldo_per_tipe(): void
    {
        Kantong::create(['nama' => 'Tunai', 'tipe' => 'harian', 'saldo_awal' => 100_000, 'tanggal_saldo_awal' => today()->toDateString(), 'urutan' => 1]);
        Kantong::create(['nama' => 'BSI', 'tipe' => 'harian', 'saldo_awal' => 250_000, 'tanggal_saldo_awal' => today()->toDateString(), 'urutan' => 2]);
        Kantong::create(['nama' => 'Tabungan', 'tipe' => 'tabungan', 'saldo_awal' => 400_000, 'tanggal_saldo_awal' => today()->toDateString(), 'urutan' => 3]);
        Kantong::create(['nama' => 'Saham', 'tipe' => 'investasi', 'saldo_awal' => 900_000, 'tanggal_saldo_awal' => today()->toDateString(), 'urutan' => 4]);

        $this->assertSame(
            ['harian' => 350_000, 'tabungan' => 400_000, 'investasi' => 900_000],
            app(SaldoService::class)->totalByTipe()
        );
    }

    public function test_api_kantong_menyertakan_tipe(): void
    {
        config(['services.hermes.token' => 'test-admin-token-yang-panjang-32-karakter']);
        Kantong::create(['nama' => 'Tabungan', 'tipe' => 'tabungan', 'saldo_awal' => 0, 'tanggal_saldo_awal' => today()->toDateString(), 'urutan' => 1]);

        $this->withHeader('Authorization', 'Bearer test-admin-token-yang-panjang-32-karakter')
            ->getJson('/api/kantong')
            ->assertOk()
            ->assertJsonPath('data.0.tipe', 'tabungan');
    }

    public function test_ringkasan_mengecualikan_hutang_dan_piutang_dari_belanja_dan_pemasukan(): void
    {
        $service = app(TransaksiService::class);

        $service->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan', 'jumlah' => 100_000, 'metode' => 'Tunai']);
        $service->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Hutang', 'jumlah' => 50_000, 'metode' => 'Tunai']);
        $service->buat(['tanggal' => today()->toDateString(), 'jenis' => 'masuk', 'kategori' => 'Piutang', 'jumlah' => 40_000, 'metode' => 'Tunai']);
        $service->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Mutasi', 'jumlah' => 25_000, 'metode' => 'Tunai', 'kantong_tujuan' => 'BSI']);

        $ringkasan = app(RingkasanService::class)->ringkasanBulan(today());

        $this->assertSame(100_000, $ringkasan['keluar']);
        $this->assertSame(0, $ringkasan['masuk']);
        $this->assertSame(50_000, $ringkasan['hutang']);
        $this->assertSame(40_000, $ringkasan['piutang']);
        $this->assertSame(25_000, $ringkasan['mutasi']);
        $this->assertSame(1, $ringkasan['jumlah_transaksi']);
    }
}
