<?php

namespace Tests\Feature;

use App\Models\KategoriMeta;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKategoriTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-admin-token-yang-panjang-32-karakter';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.hermes.token' => $this->token]);
    }

    private function api(): static
    {
        return $this->withHeader('Authorization', 'Bearer '.$this->token);
    }

    public function test_api_kategori_tanpa_token_ditolak(): void
    {
        $this->getJson('/api/kategori')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Token admin tidak valid.');
    }

    public function test_database_kosong_mengembalikan_daftar_kosong(): void
    {
        $this->api()->getJson('/api/kategori')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure(['data', 'server_time']);
    }

    public function test_daftar_kategori_menggabungkan_meta_dan_pemakaian(): void
    {
        KategoriMeta::create(['kategori' => 'Makanan', 'ikon' => 'utensils', 'warna' => '#A83B24', 'urutan' => 1]);

        $transaksi = app(TransaksiService::class);
        $transaksi->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan',
            'jumlah' => 100_000, 'pencatat' => 'David NA', 'metode' => 'Tunai',
        ]);
        $transaksi->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan',
            'jumlah' => 50_000, 'pencatat' => 'Nurul Azizah', 'metode' => 'Tunai',
        ]);
        $transaksi->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'masuk', 'kategori' => 'Gaji',
            'jumlah' => 500_000, 'pencatat' => 'David NA', 'metode' => 'BSI',
        ]);
        $transaksi->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Mutasi',
            'jumlah' => 25_000, 'pencatat' => 'David NA', 'metode' => 'Tunai', 'kantong_tujuan' => 'BSI',
        ]);

        $response = $this->api()->getJson('/api/kategori')->assertOk();
        $data = collect($response->json('data'))->keyBy('kategori');

        $makanan = $data->get('Makanan');
        $this->assertSame('utensils', $makanan['ikon']);
        $this->assertSame('#A83B24', $makanan['warna']);
        $this->assertSame(1, $makanan['urutan']);
        $this->assertTrue($makanan['ada_meta']);
        $this->assertFalse($makanan['mutasi']);
        $this->assertSame(2, $makanan['jumlah_transaksi']);
        $this->assertSame(150_000, $makanan['total']);
        $this->assertSame(150_000, $makanan['keluar']['total']);
        $this->assertSame(0, $makanan['masuk']['total']);

        $gaji = $data->get('Gaji');
        $this->assertSame('bank', $gaji['ikon']);
        $this->assertSame('#496976', $gaji['warna']);
        $this->assertFalse($gaji['ada_meta']);
        $this->assertSame(500_000, $gaji['masuk']['total']);
        $this->assertSame(0, $gaji['keluar']['total']);

        $mutasi = $data->get('Mutasi');
        $this->assertTrue($mutasi['mutasi']);
        $this->assertSame('repeat', $mutasi['ikon']);
        $this->assertSame(25_000, $mutasi['keluar']['total']);
    }

    public function test_meta_soft_deleted_memakai_fallback(): void
    {
        $meta = KategoriMeta::create(['kategori' => 'Makanan', 'ikon' => 'cart', 'warna' => '#123456', 'urutan' => 9]);
        $meta->delete();

        app(TransaksiService::class)->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan',
            'jumlah' => 10_000, 'pencatat' => 'David NA', 'metode' => 'Tunai',
        ]);

        $response = $this->api()->getJson('/api/kategori')->assertOk();
        $makanan = collect($response->json('data'))->firstWhere('kategori', 'Makanan');

        $this->assertFalse($makanan['ada_meta']);
        $this->assertSame('utensils', $makanan['ikon']);
        $this->assertSame('#496976', $makanan['warna']);
        $this->assertSame(0, $makanan['urutan']);
    }

    public function test_daftar_diurutkan_alfabetis(): void
    {
        KategoriMeta::create(['kategori' => 'Zebra', 'ikon' => 'tag', 'warna' => '#111111']);
        app(TransaksiService::class)->buat([
            'tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Apel',
            'jumlah' => 10_000, 'pencatat' => 'David NA',
        ]);

        $response = $this->api()->getJson('/api/kategori')->assertOk();

        $this->assertSame(['Apel', 'Zebra'], array_column($response->json('data'), 'kategori'));
    }
}
