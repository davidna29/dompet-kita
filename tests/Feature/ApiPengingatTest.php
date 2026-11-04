<?php

namespace Tests\Feature;

use App\Models\TransaksiBerulang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPengingatTest extends TestCase
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

    private function payload(array $override = []): array
    {
        return array_merge([
            'nama' => 'Listrik PLN',
            'kategori' => 'Tagihan',
            'jumlah' => 480_000,
            'aturan' => 'bulanan',
            'jatuh_tempo' => today()->addDays(10)->toDateString(),
            'metode' => 'BSI',
            'pencatat' => 'David NA',
        ], $override);
    }

    public function test_api_pengingat_tanpa_token_ditolak(): void
    {
        $this->getJson('/api/pengingat')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Token admin tidak valid.');
    }

    public function test_get_default_hanya_aktif_dan_semua_menampilkan_nonaktif(): void
    {
        $aktif = TransaksiBerulang::create([
            'nama' => 'Aktif', 'kategori' => 'Tagihan', 'jumlah' => 100_000,
            'aturan' => 'bulanan', 'jatuh_tempo' => today()->addDays(10)->toDateString(),
            'pencatat' => 'David NA', 'aktif' => true,
        ]);
        $nonaktif = TransaksiBerulang::create([
            'nama' => 'Nonaktif', 'kategori' => 'Tagihan', 'jumlah' => 200_000,
            'aturan' => 'bulanan', 'jatuh_tempo' => today()->addDays(5)->toDateString(),
            'pencatat' => 'Nurul Azizah', 'aktif' => false,
        ]);

        $this->api()->getJson('/api/pengingat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aktif->id);

        $semua = $this->api()->getJson('/api/pengingat?semua=1')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertEqualsCanonicalizing(
            [$aktif->id, $nonaktif->id],
            array_column($semua->json('data'), 'id')
        );
    }

    public function test_post_membuat_jadwal_dan_default_aktif(): void
    {
        $this->api()->postJson('/api/pengingat', $this->payload([
            'kategori' => 'tagihan',
            'kantong_tujuan' => 'Tunai',
        ]))
            ->assertCreated()
            ->assertJsonPath('dibuat', true)
            ->assertJsonPath('data.kategori', 'Tagihan')
            ->assertJsonPath('data.aktif', true)
            ->assertJsonPath('data.client_key', null)
            ->assertJsonPath('data.kantong_tujuan', null)
            ->assertJsonPath('data.status', 'akan-datang');

        $this->assertDatabaseHas('transaksi_berulang', [
            'nama' => 'Listrik PLN',
            'kategori' => 'Tagihan',
            'jumlah' => 480_000,
            'aktif' => true,
        ]);
    }

    public function test_post_idempoten_dengan_client_key(): void
    {
        $payload = $this->payload(['client_key' => 'TELEGRAM-1-2']);

        $pertama = $this->api()->postJson('/api/pengingat', $payload)
            ->assertCreated()
            ->assertJsonPath('data.client_key', 'TELEGRAM-1-2');

        $id = $pertama->json('data.id');

        $this->api()->postJson('/api/pengingat', $this->payload([
            'client_key' => 'TELEGRAM-1-2',
            'nama' => 'Nama berbeda',
            'jumlah' => 1,
        ]))
            ->assertOk()
            ->assertJsonPath('dibuat', false)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.nama', 'Listrik PLN')
            ->assertJsonPath('data.jumlah', 480_000);

        $this->assertDatabaseCount('transaksi_berulang', 1);
    }

    public function test_post_mutasi_wajib_asal_dan_tujuan_berbeda(): void
    {
        $this->api()->postJson('/api/pengingat', $this->payload([
            'kategori' => 'Mutasi',
        ]))
            ->assertStatus(422)
            ->assertJsonPath('error', 'Mutasi membutuhkan kantong asal dan tujuan yang berbeda.');

        $this->api()->postJson('/api/pengingat', $this->payload([
            'kategori' => 'Mutasi',
            'metode' => 'BSI',
            'kantong_tujuan' => 'BSI',
        ]))
            ->assertStatus(422)
            ->assertJsonPath('error', 'Mutasi membutuhkan kantong asal dan tujuan yang berbeda.');

        $this->api()->postJson('/api/pengingat', $this->payload([
            'kategori' => 'Mutasi',
            'metode' => 'BSI',
            'kantong_tujuan' => 'Tunai',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.metode', 'BSI')
            ->assertJsonPath('data.kantong_tujuan', 'Tunai');
    }

    public function test_post_menolak_validasi_tidak_valid(): void
    {
        $this->api()->postJson('/api/pengingat', $this->payload(['aturan' => 'harian']))
            ->assertStatus(422);

        $this->api()->postJson('/api/pengingat', $this->payload(['jumlah' => 0]))
            ->assertStatus(422);

        $this->api()->postJson('/api/pengingat', $this->payload(['jatuh_tempo' => '2026-13-01']))
            ->assertStatus(422);

        $tanpaPencatat = $this->payload();
        unset($tanpaPencatat['pencatat']);
        $this->api()->postJson('/api/pengingat', $tanpaPencatat)
            ->assertStatus(422)
            ->assertJsonPath('error', 'Pencatat wajib diisi.');

        $this->api()->postJson('/api/pengingat', $this->payload(['client_key' => str_repeat('x', 65)]))
            ->assertStatus(422);
    }

    public function test_put_memperbarui_sebagian_dan_client_key_tidak_bisa_diubah(): void
    {
        $created = $this->api()->postJson('/api/pengingat', $this->payload([
            'client_key' => 'TELEGRAM-1-3',
        ]))->assertCreated();

        $id = $created->json('data.id');

        $this->api()->putJson('/api/pengingat/'.$id, [
            'nama' => 'Listrik Baru',
            'aktif' => false,
        ])
            ->assertOk()
            ->assertJsonPath('data.nama', 'Listrik Baru')
            ->assertJsonPath('data.aktif', false)
            ->assertJsonPath('data.status', 'nonaktif')
            ->assertJsonPath('data.client_key', 'TELEGRAM-1-3');

        $this->api()->putJson('/api/pengingat/'.$id, [
            'client_key' => 'TELEGRAM-1-4',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'client_key tidak dapat diubah.');
    }

    public function test_put_pada_jadwal_soft_deleted_menghasilkan_404(): void
    {
        $created = $this->api()->postJson('/api/pengingat', $this->payload())->assertCreated();
        $id = $created->json('data.id');

        $this->api()->deleteJson('/api/pengingat/'.$id)->assertOk();

        $this->api()->putJson('/api/pengingat/'.$id, ['nama' => 'Tidak boleh'])
            ->assertNotFound();
    }

    public function test_delete_soft_dan_idempoten(): void
    {
        $created = $this->api()->postJson('/api/pengingat', $this->payload())->assertCreated();
        $id = $created->json('data.id');

        $this->api()->deleteJson('/api/pengingat/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true)
            ->assertJsonPath('data.deleted_at', fn ($value) => $value !== null);

        $this->assertSoftDeleted('transaksi_berulang', ['id' => $id]);

        $this->api()->deleteJson('/api/pengingat/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true);

        $this->api()->deleteJson('/api/pengingat/999999')
            ->assertNotFound()
            ->assertJsonPath('error', 'Data tidak ditemukan.');

        $this->api()->getJson('/api/pengingat')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->api()->getJson('/api/pengingat?semua=1')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_post_dengan_client_key_terhapus_mengembalikan_baris_terhapus(): void
    {
        $payload = $this->payload(['client_key' => 'TELEGRAM-1-5']);

        $created = $this->api()->postJson('/api/pengingat', $payload)->assertCreated();
        $id = $created->json('data.id');

        $this->api()->deleteJson('/api/pengingat/'.$id)->assertOk();

        $this->api()->postJson('/api/pengingat', $payload)
            ->assertOk()
            ->assertJsonPath('dibuat', false)
            ->assertJsonPath('data.id', $id)
            ->assertJsonPath('data.deleted_at', fn ($value) => $value !== null);

        $this->assertDatabaseCount('transaksi_berulang', 1);
    }
}
