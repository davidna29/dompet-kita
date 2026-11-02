<?php

namespace Tests\Feature;

use App\Models\Kantong;
use App\Models\TransaksiBerulang;
use App\Models\User;
use App\Services\SaldoService;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KantongManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_kantong_with_an_opening_balance(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);

        $this->actingAs($user)
            ->post(route('kantong.store'), [
                'nama' => 'Bank Baru',
                'saldo_awal' => '125.000',
                'tanggal_saldo_awal' => '2026-10-01',
            ])
            ->assertRedirect(route('kantong'))
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('kantong', [
            'nama' => 'Bank Baru',
            'saldo_awal' => 125_000,
            'tanggal_saldo_awal' => '2026-10-01',
            'deleted_at' => null,
        ]);
    }

    public function test_rename_updates_live_deleted_and_recurring_references_without_changing_balance(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $kantong = Kantong::create([
            'nama' => 'BSI',
            'saldo_awal' => 1_000_000,
            'tanggal_saldo_awal' => today()->toDateString(),
            'urutan' => 1,
        ]);
        Kantong::create([
            'nama' => 'Tunai',
            'saldo_awal' => 0,
            'tanggal_saldo_awal' => today()->toDateString(),
            'urutan' => 2,
        ]);

        $transaksi = app(TransaksiService::class);
        $belanja = $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 100_000,
            'metode' => 'BSI',
        ]);
        $mutasiMasuk = $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Mutasi',
            'jumlah' => 50_000,
            'metode' => 'Tunai',
            'kantong_tujuan' => 'BSI',
        ]);
        $dihapus = $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'masuk',
            'kategori' => 'Honor',
            'jumlah' => 250_000,
            'metode' => 'BSI',
        ]);
        $transaksi->hapus($dihapus, $user);

        TransaksiBerulang::create([
            'nama' => 'Honor rutin',
            'kategori' => 'Honor',
            'jumlah' => 250_000,
            'aturan' => 'bulanan',
            'jatuh_tempo' => today()->addMonth()->toDateString(),
            'metode' => 'BSI',
            'pencatat' => 'David NA',
            'aktif' => true,
        ]);

        $saldoSebelum = app(SaldoService::class)->hitung($kantong)['saldo'];

        $this->actingAs($user)
            ->put(route('kantong.update', $kantong), ['nama' => 'Bank Utama'])
            ->assertRedirect(route('kantong'))
            ->assertSessionHas('sukses');

        $this->assertDatabaseHas('kantong', ['id' => $kantong->id, 'nama' => 'Bank Utama']);
        $this->assertDatabaseHas('transaksi', ['id' => $belanja->id, 'metode' => 'Bank Utama']);
        $this->assertDatabaseHas('transaksi', ['id' => $mutasiMasuk->id, 'kantong_tujuan' => 'Bank Utama']);
        $this->assertDatabaseHas('transaksi', ['id' => $dihapus->id, 'metode' => 'Bank Utama']);
        $this->assertSoftDeleted('transaksi', ['id' => $dihapus->id]);
        $this->assertDatabaseHas('transaksi_berulang', ['metode' => 'Bank Utama']);
        $this->assertDatabaseHas('transaksi_riwayat', [
            'transaksi_id' => $belanja->id,
            'aksi' => 'diubah',
            'oleh' => 'David NA',
        ]);
        $this->assertSame($saldoSebelum, app(SaldoService::class)->hitung($kantong->fresh())['saldo']);
    }

    public function test_rename_rejects_a_mutasi_that_would_use_the_same_source_and_destination(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $bsi = Kantong::create(['nama' => 'BSI', 'saldo_awal' => 0, 'tanggal_saldo_awal' => today(), 'urutan' => 1]);
        Kantong::create(['nama' => 'Tunai', 'saldo_awal' => 0, 'tanggal_saldo_awal' => today(), 'urutan' => 2]);
        $mutasi = app(TransaksiService::class)->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Mutasi',
            'jumlah' => 25_000,
            'metode' => 'BSI',
            'kantong_tujuan' => 'Tunai',
        ]);

        $this->actingAs($user)
            ->put(route('kantong.update', $bsi), ['nama' => 'Tunai'])
            ->assertSessionHasErrors('nama');

        $this->assertDatabaseHas('kantong', ['id' => $bsi->id, 'nama' => 'BSI']);
        $this->assertDatabaseHas('transaksi', ['id' => $mutasi->id, 'metode' => 'BSI', 'kantong_tujuan' => 'Tunai']);
    }

    public function test_archive_requires_zero_balance_and_no_active_schedule_then_preserves_history(): void
    {
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $kantong = Kantong::create([
            'nama' => 'Dompet Lama',
            'saldo_awal' => 500_000,
            'tanggal_saldo_awal' => today()->toDateString(),
            'urutan' => 1,
        ]);
        $transaksi = app(TransaksiService::class);
        $masuk = $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'masuk',
            'kategori' => 'Gaji',
            'jumlah' => 500_000,
            'metode' => 'Dompet Lama',
        ]);
        $keluar = $transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 500_000,
            'metode' => 'Dompet Lama',
        ]);

        $jadwal = TransaksiBerulang::create([
            'nama' => 'Iuran dari dompet lama',
            'kategori' => 'Tagihan',
            'jumlah' => 10_000,
            'aturan' => 'bulanan',
            'jatuh_tempo' => today()->addMonth()->toDateString(),
            'metode' => 'Dompet Lama',
            'pencatat' => 'David NA',
            'aktif' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('kantong.destroy', $kantong))
            ->assertSessionHasErrors('kantong');
        $this->assertNotSoftDeleted('kantong', ['id' => $kantong->id]);

        $kantong->update(['saldo_awal' => 0]);
        $this->delete(route('kantong.destroy', $kantong))->assertSessionHasErrors('kantong');
        $jadwal->update(['aktif' => false]);

        $this->delete(route('kantong.destroy', $kantong))
            ->assertRedirect(route('kantong'))
            ->assertSessionHas('sukses');

        $this->assertSoftDeleted('kantong', ['id' => $kantong->id]);
        $this->assertDatabaseHas('transaksi', ['id' => $masuk->id, 'metode' => 'Dompet Lama']);
        $this->assertDatabaseHas('transaksi', ['id' => $keluar->id, 'metode' => 'Dompet Lama']);
        $this->get(route('kantong'))
            ->assertOk()
            ->assertSee('Kantong diarsipkan')
            ->assertSee('Dompet Lama')
            ->assertDontSee('Metode di luar kantong terdaftar');

        $this->post(route('kantong.pulihkan', $kantong->id))->assertRedirect(route('kantong'));
        $this->assertNotSoftDeleted('kantong', ['id' => $kantong->id]);
    }

    public function test_archived_kantong_stays_out_of_the_active_balance_api(): void
    {
        config(['services.hermes.token' => 'test-admin-token-yang-panjang-32-karakter']);
        $user = User::factory()->create(['pencatat' => 'David NA']);
        $aktif = Kantong::create(['nama' => 'Tunai', 'saldo_awal' => 0, 'tanggal_saldo_awal' => today(), 'urutan' => 1]);
        $arsip = Kantong::create(['nama' => 'Lama', 'saldo_awal' => 0, 'tanggal_saldo_awal' => today(), 'urutan' => 2]);

        $this->actingAs($user)->delete(route('kantong.destroy', $arsip))->assertRedirect(route('kantong'));

        $this->withHeader('Authorization', 'Bearer test-admin-token-yang-panjang-32-karakter')
            ->getJson('/api/kantong')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aktif->id)
            ->assertJsonStructure(['data' => [[
                'id', 'nama', 'saldo_awal', 'tanggal_saldo_awal', 'masuk', 'keluar', 'terima_mutasi', 'saldo', 'urutan',
            ]]]);
    }
}
