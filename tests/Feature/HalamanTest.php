<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Kantong;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_dapat_dibuka(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke papan.');
    }

    public function test_semua_halaman_utama_dapat_dibuka_setelah_login(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);

        $this->actingAs($user);

        foreach ([
            '/beranda',
            '/transaksi',
            '/transaksi/baru',
            '/ringkasan',
            '/kantong',
            '/anggaran',
            '/berulang',
            '/berulang/baru',
            '/kategori',
            '/pengaturan',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_ekspor_csv_memakai_header_stb_dan_digit_polos(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $this->actingAs($user);

        app(\App\Services\TransaksiService::class)->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'keterangan' => 'Belanja',
            'pencatat' => 'David NA',
            'metode' => 'Tunai',
            'sumber' => 'ui',
        ]);

        $response = $this->get('/transaksi/ekspor/csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $isi = $response->streamedContent();
        $this->assertStringContainsString('tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan', $isi);
        $this->assertStringContainsString(today()->toDateString().',keluar,Makanan,35000', $isi);
    }

    public function test_saldo_awal_dan_tanggal_hitung_dapat_diatur(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $this->actingAs($user);

        $kantong = Kantong::create([
            'nama' => 'Tunai',
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => today()->subMonth()->toDateString(),
            'urutan' => 1,
        ]);

        $this->post('/kantong/saldo-awal', [
            'saldo_awal' => [$kantong->id => '0'],
            'tanggal_saldo_awal' => [$kantong->id => '2026-01-01'],
        ])->assertRedirect(route('kantong'));

        $this->assertDatabaseHas('kantong', [
            'id' => $kantong->id,
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => '2026-01-01',
        ]);
    }
}
