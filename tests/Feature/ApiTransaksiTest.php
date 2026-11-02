<?php

namespace Tests\Feature;

use App\Models\TransaksiBerulang;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTransaksiTest extends TestCase
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

    public function test_api_tanpa_token_ditolak(): void
    {
        $this->getJson('/api/health')->assertStatus(401)->assertJson(['error' => 'Token admin tidak valid.']);
    }

    public function test_health_dengan_token(): void
    {
        $this->api()->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonStructure(['server_time', 'timezone']);
    }

    public function test_post_idempoten_dengan_client_key(): void
    {
        $data = [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'keterangan' => 'Belanja pasar',
            'pencatat' => 'David NA',
            'metode' => 'Tunai',
            'sumber' => 'telegram',
            'client_key' => 'TELEGRAM-123-456',
        ];

        $pertama = $this->api()->postJson('/api/transaksi', $data)->assertStatus(201);
        $id = $pertama->json('data.id');

        $kedua = $this->api()->postJson('/api/transaksi', $data)->assertStatus(200);

        $this->assertSame($id, $kedua->json('data.id'));
        $this->assertDatabaseCount('transaksi', 1);
    }

    public function test_api_metode_kantong_tetap_teks_bebas_sampai_hermes_menyinkronkan_daftar(): void
    {
        $response = $this->api()->postJson('/api/transaksi', [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'pencatat' => 'David NA',
            'metode' => 'Shoppe',
            'sumber' => 'telegram',
        ])->assertCreated();

        $this->assertSame('Shoppe', $response->json('data.metode'));
    }

    public function test_mutasi_wajib_punya_asal_dan_tujuan_berbeda(): void
    {
        $data = [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Mutasi',
            'jumlah' => 500000,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
        ];

        $this->api()->postJson('/api/transaksi', $data)
            ->assertStatus(422)
            ->assertJsonPath('error', 'Mutasi wajib memilih kantong asal dan kantong tujuan.');

        $data['kantong_tujuan'] = 'BSI';
        $this->api()->postJson('/api/transaksi', $data)
            ->assertStatus(422);

        $data['kantong_tujuan'] = 'Tunai';
        $this->api()->postJson('/api/transaksi', $data)->assertStatus(201);
    }

    public function test_update_dan_soft_delete_muncul_di_since(): void
    {
        $data = [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'pencatat' => 'David NA',
            'sumber' => 'telegram',
        ];

        $id = $this->api()->postJson('/api/transaksi', $data)->json('data.id');

        $this->api()->putJson('/api/transaksi/'.$id, ['jumlah' => 40000])
            ->assertOk()
            ->assertJsonPath('data.jumlah', 40000);

        $this->api()->deleteJson('/api/transaksi/'.$id)
            ->assertOk()
            ->assertJsonPath('data.deleted_at', fn ($value) => $value !== null);

        $this->api()->getJson('/api/transaksi?since=2000-01-01T00:00:00')
            ->assertOk()
            ->assertJsonPath('data.0.id', $id)
            ->assertJsonPath('data.0.deleted_at', fn ($value) => $value !== null);

        $this->assertSoftDeleted('transaksi', ['id' => $id]);
    }

    public function test_put_tanpa_perubahan_tidak_menggeser_updated_at(): void
    {
        $data = [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'pencatat' => 'David NA',
            'sumber' => 'telegram',
        ];

        $dibuat = $this->api()->postJson('/api/transaksi', $data)->assertCreated();
        $id = $dibuat->json('data.id');
        $updatedAtAwal = $dibuat->json('data.updated_at');

        Carbon::setTestNow(Carbon::parse($updatedAtAwal, 'Asia/Jakarta')->addMinute());

        try {
            $tetap = $this->api()->putJson('/api/transaksi/'.$id, ['jumlah' => 35000])
                ->assertOk()
                ->json('data.updated_at');
        } finally {
            Carbon::setTestNow();
        }

        $this->assertSame($updatedAtAwal, $tetap);
    }

    public function test_delete_berulang_pada_baris_soft_deleted_idempoten(): void
    {
        $data = [
            'tanggal' => '2026-11-02',
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 35000,
            'pencatat' => 'David NA',
            'sumber' => 'telegram',
        ];

        $dibuat = $this->api()->postJson('/api/transaksi', $data)->assertCreated();
        $id = $dibuat->json('data.id');
        $hapusPertama = $this->api()->deleteJson('/api/transaksi/'.$id)->assertOk();
        $deletedAt = $hapusPertama->json('data.deleted_at');

        $this->api()->deleteJson('/api/transaksi/'.$id)
            ->assertOk()
            ->assertJsonPath('data.deleted_at', $deletedAt);

        $this->assertDatabaseCount('transaksi', 1);
        $this->assertDatabaseCount('transaksi_riwayat', 2);
        $this->assertDatabaseHas('transaksi_riwayat', ['transaksi_id' => $id, 'aksi' => 'dihapus']);
    }

    public function test_list_memakai_amplop_data_next_offset_dan_server_time(): void
    {
        $this->api()->getJson('/api/transaksi?limit=2')
            ->assertOk()
            ->assertJsonStructure(['data', 'next_offset', 'server_time']);
    }

    public function test_pesan_validasi_api_dalam_bahasa_indonesia(): void
    {
        $this->api()->postJson('/api/transaksi', [])
            ->assertStatus(422)
            ->assertJsonStructure(['error'])
            ->assertJsonPath('error', fn ($pesan) => is_string($pesan) && str_contains($pesan, 'wajib diisi'));
    }

    public function test_mutasi_dengan_jenis_masuk_ditolak(): void
    {
        $this->api()->postJson('/api/transaksi', [
            'tanggal' => '2026-11-02',
            'jenis' => 'masuk',
            'kategori' => 'Mutasi',
            'jumlah' => 100000,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
            'kantong_tujuan' => 'Tunai',
        ])->assertStatus(422)->assertJsonPath('error', 'Mutasi selalu berjenis keluar.');
    }

    public function test_konfirmasi_pengingat_api_idempoten(): void
    {
        $jadwal = TransaksiBerulang::create([
            'nama' => 'Listrik PLN',
            'kategori' => 'Tagihan',
            'jumlah' => 480000,
            'aturan' => 'bulanan',
            'jatuh_tempo' => '2026-11-05',
            'metode' => 'BSI',
            'pencatat' => 'David NA',
            'aktif' => true,
        ]);

        $payload = ['jumlah' => 480000, 'jatuh_tempo' => '2026-11-05'];

        $pertama = $this->api()
            ->postJson('/api/pengingat/'.$jadwal->id.'/konfirmasi', $payload)
            ->assertStatus(201)
            ->assertJsonPath('dibuat', true);

        $kedua = $this->api()
            ->postJson('/api/pengingat/'.$jadwal->id.'/konfirmasi', $payload)
            ->assertStatus(200)
            ->assertJsonPath('dibuat', false);

        $this->assertSame($pertama->json('data.id'), $kedua->json('data.id'));
        $this->assertDatabaseCount('transaksi', 1);
    }

    public function test_since_memakai_zona_waktu_jakarta_bukan_utc(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));

        Carbon::setTestNow(Carbon::create(2026, 11, 2, 15, 0, 0, 'Asia/Jakarta'));

        try {
            $this->api()->postJson('/api/transaksi', [
                'tanggal' => '2026-11-02',
                'jenis' => 'keluar',
                'kategori' => 'Makanan',
                'jumlah' => 35000,
                'pencatat' => 'David NA',
                'sumber' => 'telegram',
                'client_key' => 'WIB-BOUNDARY',
            ])->assertStatus(201);

            $this->api()->getJson('/api/transaksi?since=2026-11-02T14:00:00')
                ->assertOk()
                ->assertJsonCount(1, 'data');

            $this->api()->getJson('/api/transaksi?since=2026-11-02T16:00:00')
                ->assertOk()
                ->assertJsonCount(0, 'data');
        } finally {
            Carbon::setTestNow();
        }
    }
}
