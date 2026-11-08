<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHutangTest extends TestCase
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
            'pihak' => 'Pak Andi',
            'jenis' => 'hutang',
            'nominal' => 500_000,
            'tanggal' => today()->toDateString(),
            'jatuh_tempo' => today()->addDays(30)->toDateString(),
            'keterangan' => 'Pinjam servis motor',
        ], $override);
    }

    public function test_api_hutang_tanpa_token_ditolak(): void
    {
        $this->getJson('/api/hutang')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Token admin tidak valid.');
    }

    public function test_store_membuat_catatan_dengan_sisa_penuh_tanpa_transaksi(): void
    {
        $this->api()->postJson('/api/hutang', $this->payload(['jenis' => 'piutang', 'pihak' => '  Mbak   Sari  ']))
            ->assertCreated()
            ->assertJsonPath('data.pihak', 'Mbak Sari')
            ->assertJsonPath('data.jenis', 'piutang')
            ->assertJsonPath('data.nominal', 500_000)
            ->assertJsonPath('data.sisa', 500_000)
            ->assertJsonPath('data.status', 'belum');

        $this->assertDatabaseCount('hutang_piutang', 1);
        $this->assertDatabaseCount('transaksi', 0);
    }

    public function test_bayar_sebagian_mengurangi_sisa_dan_membuat_transaksi(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload())->json('data.id');

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', [
            'jumlah' => 200_000,
            'tanggal' => today()->toDateString(),
            'metode' => 'BSI',
            'keterangan' => 'Cicilan pertama',
            'pencatat' => 'David NA',
        ])
            ->assertCreated()
            ->assertJsonPath('dibuat', true)
            ->assertJsonPath('data.sisa', 300_000)
            ->assertJsonPath('data.status', 'belum')
            ->assertJsonPath('transaksi.jenis', 'keluar')
            ->assertJsonPath('transaksi.kategori', 'Hutang')
            ->assertJsonPath('transaksi.jumlah', 200_000)
            ->assertJsonPath('transaksi.client_key', 'HUTANG-'.$id.'-'.today()->format('Ymd').'-200000');

        $this->assertDatabaseCount('transaksi', 1);
    }

    public function test_piutang_membuat_transaksi_masuk(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload(['jenis' => 'piutang', 'pihak' => 'Mbak Sari']))->json('data.id');

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', [
            'jumlah' => 500_000,
            'tanggal' => today()->toDateString(),
            'pencatat' => 'Nurul Azizah',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'lunas')
            ->assertJsonPath('data.sisa', 0)
            ->assertJsonPath('transaksi.jenis', 'masuk')
            ->assertJsonPath('transaksi.kategori', 'Piutang');
    }

    public function test_bayar_lunas_menandai_status_lunas(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload())->json('data.id');

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', [
            'jumlah' => 500_000,
            'tanggal' => today()->toDateString(),
            'pencatat' => 'David NA',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'lunas')
            ->assertJsonPath('data.sisa', 0);

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', [
            'jumlah' => 1,
            'tanggal' => today()->toDateString(),
            'pencatat' => 'David NA',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Catatan ini sudah lunas.');
    }

    public function test_bayar_idempoten_tidak_mengurangi_sisa_dua_kali(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload())->json('data.id');

        $payload = [
            'jumlah' => 250_000,
            'tanggal' => today()->toDateString(),
            'pencatat' => 'David NA',
        ];

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', $payload)
            ->assertCreated()
            ->assertJsonPath('data.sisa', 250_000);

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', $payload)
            ->assertOk()
            ->assertJsonPath('dibuat', false)
            ->assertJsonPath('data.sisa', 250_000);

        $this->assertDatabaseCount('transaksi', 1);
    }

    public function test_bayar_melebihi_sisa_ditolak(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload())->json('data.id');

        $this->api()->postJson('/api/hutang/'.$id.'/bayar', [
            'jumlah' => 500_001,
            'tanggal' => today()->toDateString(),
            'pencatat' => 'David NA',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Jumlah bayar melebihi sisa 500000.');

        $this->assertDatabaseCount('transaksi', 0);
    }

    public function test_validasi_store(): void
    {
        $this->api()->postJson('/api/hutang', $this->payload(['pihak' => '']))
            ->assertStatus(422)
            ->assertJsonPath('error', 'Nama pihak wajib diisi.');

        $this->api()->postJson('/api/hutang', $this->payload(['jenis' => 'pinjaman']))
            ->assertStatus(422)
            ->assertJsonPath('error', 'Jenis harus hutang atau piutang.');

        $this->api()->postJson('/api/hutang', $this->payload(['nominal' => 0]))
            ->assertStatus(422);
    }

    public function test_index_filter_jenis_dan_status(): void
    {
        $this->api()->postJson('/api/hutang', $this->payload(['pihak' => 'Hutang A']))->assertCreated();
        $this->api()->postJson('/api/hutang', $this->payload(['jenis' => 'piutang', 'pihak' => 'Piutang B']))->assertCreated();

        $this->api()->getJson('/api/hutang?jenis=piutang')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.pihak', 'Piutang B');

        $this->api()->getJson('/api/hutang?status=lunas')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_delete_soft_dan_idempoten(): void
    {
        $id = $this->api()->postJson('/api/hutang', $this->payload())->json('data.id');

        $this->api()->deleteJson('/api/hutang/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true)
            ->assertJsonPath('data.deleted_at', fn ($value) => $value !== null);

        $this->assertSoftDeleted('hutang_piutang', ['id' => $id]);

        $this->api()->deleteJson('/api/hutang/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true);

        $this->api()->getJson('/api/hutang')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->api()->deleteJson('/api/hutang/999999')
            ->assertNotFound()
            ->assertJsonPath('error', 'Data tidak ditemukan.');
    }
}
