<?php

namespace Tests\Feature;

use App\Models\HutangPiutang;
use App\Models\Kantong;
use App\Models\User;
use App\Services\TransaksiService;
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
            '/kantong/baru',
            '/anggaran',
            '/berulang',
            '/berulang/baru',
            '/hutang',
            '/hutang/baru',
            '/kategori',
            '/pengaturan',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_hutang_dapat_dicatat_dan_dibayar_dari_ui(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $this->actingAs($user);

        $this->post('/hutang', [
            'pihak' => 'Pak Andi',
            'jenis' => 'hutang',
            'nominal' => '500.000',
            'tanggal' => today()->toDateString(),
            'jatuh_tempo' => today()->addDays(30)->toDateString(),
        ])->assertRedirect();

        $hutang = HutangPiutang::query()->firstOrFail();
        $this->assertSame(500_000, $hutang->sisa);

        $this->get('/hutang/'.$hutang->id)->assertOk();

        $this->post('/hutang/'.$hutang->id.'/bayar', [
            'jumlah' => '200.000',
            'tanggal' => today()->toDateString(),
            'metode' => 'BSI',
        ])->assertRedirect(route('hutang.show', $hutang));

        $hutang->refresh();
        $this->assertSame(300_000, $hutang->sisa);
        $this->assertDatabaseHas('transaksi', [
            'jenis' => 'keluar',
            'kategori' => 'Hutang',
            'jumlah' => 200_000,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
        ]);
    }

    public function test_ekspor_csv_memakai_header_stb_dan_digit_polos(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $this->actingAs($user);

        app(TransaksiService::class)->buat([
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
