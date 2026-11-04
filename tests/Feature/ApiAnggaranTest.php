<?php

namespace Tests\Feature;

use App\Models\Anggaran;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAnggaranTest extends TestCase
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

    public function test_api_anggaran_tanpa_token_ditolak(): void
    {
        $this->getJson('/api/anggaran')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'Token admin tidak valid.');

        $this->withHeader('Authorization', 'Bearer salah')
            ->getJson('/api/anggaran')
            ->assertUnauthorized();
    }

    public function test_get_anggaran_mengembalikan_snapshot_bulan_berjalan(): void
    {
        Anggaran::create(['periode' => now()->format('Y-m'), 'kategori' => null, 'jumlah' => 7_500_000, 'ambang' => 80]);
        Anggaran::create(['periode' => now()->format('Y-m'), 'kategori' => 'Makanan', 'jumlah' => 2_200_000, 'ambang' => 75]);

        $this->api()->getJson('/api/anggaran')
            ->assertOk()
            ->assertJsonPath('data.periode', now()->format('Y-m'))
            ->assertJsonPath('data.dipinjam', false)
            ->assertJsonPath('data.total_anggaran', 7_500_000)
            ->assertJsonPath('data.total_terpakai', 0)
            ->assertJsonPath('data.rows.0.kategori', 'Makanan')
            ->assertJsonPath('data.rows.0.status', 'aman')
            ->assertJsonStructure([
                'data' => [
                    'periode', 'dipinjam', 'periode_sebelumnya', 'total_anggaran',
                    'total_terpakai', 'total', 'rows',
                ],
                'server_time',
            ]);
    }

    public function test_get_anggaran_meminjam_bulan_sebelumnya_dengan_id_null(): void
    {
        $sebelumnya = now()->subMonthNoOverflow()->format('Y-m');
        Anggaran::create(['periode' => $sebelumnya, 'kategori' => 'Makanan', 'jumlah' => 2_000_000, 'ambang' => 80]);

        $response = $this->api()->getJson('/api/anggaran?periode='.now()->format('Y-m'))
            ->assertOk()
            ->assertJsonPath('data.dipinjam', true)
            ->assertJsonPath('data.periode_sebelumnya', $sebelumnya)
            ->assertJsonPath('data.rows.0.kategori', 'Makanan');

        $this->assertNull($response->json('data.rows.0.id'));
    }

    public function test_post_anggaran_membuat_dan_memperbarui_baris_kategori(): void
    {
        $periode = now()->format('Y-m');

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'makanan',
            'jumlah' => 2_000_000,
            'ambang' => 75,
        ])
            ->assertCreated()
            ->assertJsonPath('dibuat', true)
            ->assertJsonPath('row.kategori', 'Makanan')
            ->assertJsonPath('row.anggaran', 2_000_000)
            ->assertJsonPath('row.ambang', 75);

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'MAKANAN',
            'jumlah' => 2_500_000,
            'ambang' => 80,
        ])
            ->assertOk()
            ->assertJsonPath('dibuat', false)
            ->assertJsonPath('row.kategori', 'Makanan')
            ->assertJsonPath('row.anggaran', 2_500_000);

        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseHas('anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 2_500_000,
        ]);
    }

    public function test_post_anggaran_total_bulanan_dan_alias_total(): void
    {
        $periode = now()->format('Y-m');

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => null,
            'jumlah' => 7_500_000,
        ])
            ->assertCreated()
            ->assertJsonPath('row.kategori', 'Total bulanan')
            ->assertJsonPath('data.total_anggaran', 7_500_000);

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => '__total__',
            'jumlah' => 8_000_000,
            'ambang' => 90,
        ])
            ->assertOk()
            ->assertJsonPath('data.total_anggaran', 8_000_000)
            ->assertJsonPath('row.ambang', 90);

        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseHas('anggaran', [
            'periode' => $periode,
            'kategori' => null,
            'jumlah' => 8_000_000,
        ]);
    }

    public function test_post_anggaran_menolak_mutasi_dan_validasi_tidak_valid(): void
    {
        $periode = now()->format('Y-m');

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'Mutasi',
            'jumlah' => 100_000,
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Mutasi bukan belanja; anggaran Mutasi tidak dihitung.');

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 0,
        ])->assertStatus(422);

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 100_000,
            'ambang' => 101,
        ])->assertStatus(422);

        $this->api()->postJson('/api/anggaran', [
            'periode' => '2026-13',
            'kategori' => 'Makanan',
            'jumlah' => 100_000,
        ])->assertStatus(422);

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'jumlah' => 100_000,
        ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'Kategori wajib dikirim; gunakan null untuk total bulanan.');
    }

    public function test_post_anggaran_memulihkan_baris_soft_deleted(): void
    {
        $periode = now()->format('Y-m');
        $created = $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 1_000_000,
        ])->assertCreated();

        Anggaran::query()->findOrFail($created->json('row.id'))->delete();
        $this->assertSoftDeleted('anggaran', ['periode' => $periode, 'kategori' => 'Makanan']);

        $this->api()->postJson('/api/anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 1_500_000,
        ])
            ->assertOk()
            ->assertJsonPath('dibuat', false)
            ->assertJsonPath('row.anggaran', 1_500_000);

        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseHas('anggaran', [
            'periode' => $periode,
            'kategori' => 'Makanan',
            'jumlah' => 1_500_000,
            'deleted_at' => null,
        ]);
    }

    public function test_delete_anggaran_soft_dan_idempoten(): void
    {
        $created = $this->api()->postJson('/api/anggaran', [
            'periode' => now()->format('Y-m'),
            'kategori' => 'Makanan',
            'jumlah' => 1_000_000,
        ])->assertCreated();

        $id = $created->json('row.id');

        $this->api()->deleteJson('/api/anggaran/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true);

        $this->assertSoftDeleted('anggaran', ['id' => $id]);

        $this->api()->deleteJson('/api/anggaran/'.$id)
            ->assertOk()
            ->assertJsonPath('dihapus', true);

        $this->api()->deleteJson('/api/anggaran/999999')
            ->assertNotFound()
            ->assertJsonPath('error', 'Data tidak ditemukan.');
    }

    public function test_salin_anggaran_dari_bulan_lalu(): void
    {
        $sebelumnya = now()->subMonthNoOverflow()->format('Y-m');
        $periode = now()->format('Y-m');

        Anggaran::create(['periode' => $sebelumnya, 'kategori' => null, 'jumlah' => 7_500_000, 'ambang' => 80]);
        Anggaran::create(['periode' => $sebelumnya, 'kategori' => 'Makanan', 'jumlah' => 2_000_000, 'ambang' => 75]);

        $this->api()->postJson('/api/anggaran/salin', ['periode' => $periode])
            ->assertOk()
            ->assertJsonPath('jumlah_disalin', 2)
            ->assertJsonPath('dibuat', true)
            ->assertJsonPath('data.total_anggaran', 7_500_000);

        $this->assertDatabaseCount('anggaran', 4);

        $this->api()->postJson('/api/anggaran/salin', ['periode' => $periode])
            ->assertOk()
            ->assertJsonPath('jumlah_disalin', 0)
            ->assertJsonPath('dibuat', false);
    }

    public function test_pemakaian_anggaran_hanya_belanja_non_mutasi(): void
    {
        $transaksi = app(TransaksiService::class);
        $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 100_000,
            'pencatat' => 'David NA',
            'metode' => 'Tunai',
        ]);
        $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Mutasi',
            'jumlah' => 50_000,
            'pencatat' => 'David NA',
            'metode' => 'Tunai',
            'kantong_tujuan' => 'BSI',
        ]);
        $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'masuk',
            'kategori' => 'Gaji',
            'jumlah' => 500_000,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
        ]);

        Anggaran::create(['periode' => now()->format('Y-m'), 'kategori' => 'Makanan', 'jumlah' => 200_000, 'ambang' => 80]);

        $this->api()->getJson('/api/anggaran')
            ->assertOk()
            ->assertJsonPath('data.rows.0.kategori', 'Makanan')
            ->assertJsonPath('data.rows.0.terpakai', 100_000)
            ->assertJsonPath('data.rows.0.persen', 50)
            ->assertJsonPath('data.rows.0.status', 'aman')
            ->assertJsonPath('data.total_terpakai', 100_000);
    }
}
