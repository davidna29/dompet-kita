<?php

namespace Tests\Feature;

use App\Models\Anggaran;
use App\Models\Kantong;
use App\Models\TransaksiBerulang;
use App\Services\AnggaranService;
use App\Services\PengingatService;
use App\Services\RingkasanService;
use App\Services\SaldoService;
use App\Services\TransaksiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerhitunganTest extends TestCase
{
    use RefreshDatabase;

    private TransaksiService $transaksi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transaksi = app(TransaksiService::class);
    }

    public function test_saldo_kantong_memasukkan_mutasi_tetapi_belanja_tidak(): void
    {
        $mulai = today()->subMonth()->toDateString();

        $bsi = Kantong::create(['nama' => 'BSI', 'saldo_awal' => 1_000_000, 'tanggal_saldo_awal' => $mulai, 'urutan' => 1]);
        $tunai = Kantong::create(['nama' => 'Tunai', 'saldo_awal' => 0, 'tanggal_saldo_awal' => $mulai, 'urutan' => 2]);

        $this->transaksi->buat(['tanggal' => today()->toDateString(), 'jenis' => 'masuk', 'kategori' => 'Gaji', 'jumlah' => 500_000, 'pencatat' => 'David NA', 'metode' => 'BSI']);
        $this->transaksi->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan', 'jumlah' => 200_000, 'pencatat' => 'David NA', 'metode' => 'BSI']);
        $this->transaksi->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Mutasi', 'jumlah' => 300_000, 'pencatat' => 'David NA', 'metode' => 'BSI', 'kantong_tujuan' => 'Tunai']);

        $saldo = app(SaldoService::class);

        $this->assertSame(1_000_000, $saldo->hitung($bsi->fresh())['saldo']);
        $this->assertSame(300_000, $saldo->hitung($tunai->fresh())['saldo']);

        $rekonsiliasi = collect($saldo->rekonsiliasi([$bsi->id => '950.000']))->firstWhere('id', $bsi->id);
        $this->assertSame(950_000, $rekonsiliasi['saldo_nyata']);
        $this->assertSame(50_000, $rekonsiliasi['selisih']);

        $ringkasan = app(RingkasanService::class)->ringkasan(today()->startOfMonth(), today()->endOfMonth());

        $this->assertSame(200_000, $ringkasan['keluar']);
        $this->assertSame(300_000, $ringkasan['mutasi']);
        $this->assertSame(500_000, $ringkasan['masuk']);
        $this->assertSame(300_000, $ringkasan['selisih']);
    }

    public function test_anggaran_menghitung_transaksi_non_mutasi_dan_ambang(): void
    {
        $periode = now()->format('Y-m');
        Anggaran::create(['periode' => $periode, 'kategori' => 'Makanan', 'jumlah' => 1_000_000, 'ambang' => 80]);

        $this->transaksi->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Makanan', 'jumlah' => 800_000, 'pencatat' => 'David NA', 'metode' => 'Tunai']);
        $this->transaksi->buat(['tanggal' => today()->toDateString(), 'jenis' => 'keluar', 'kategori' => 'Mutasi', 'jumlah' => 300_000, 'pencatat' => 'David NA', 'metode' => 'BSI', 'kantong_tujuan' => 'Tunai']);

        $hasil = app(AnggaranService::class)->untukPeriode($periode);
        $baris = $hasil['rows']->firstWhere('kategori', 'Makanan');

        $this->assertSame(800_000, $baris['terpakai']);
        $this->assertSame(80, $baris['persen']);
        $this->assertSame('hampir', $baris['status']);
    }

    public function test_anggaran_dihapus_lunak_dan_dipulihkan_saat_disimpan_lagi(): void
    {
        $periode = now()->format('Y-m');
        $anggaran = Anggaran::create(['periode' => $periode, 'kategori' => 'Makanan', 'jumlah' => 1_000_000, 'ambang' => 80]);
        $service = app(AnggaranService::class);

        $service->hapus($anggaran);
        $this->assertSoftDeleted('anggaran', ['id' => $anggaran->id]);

        $service->simpan($periode, 'Makanan', 900_000, 75);

        $this->assertDatabaseCount('anggaran', 1);
        $this->assertDatabaseHas('anggaran', [
            'id' => $anggaran->id,
            'jumlah' => 900_000,
            'ambang' => 75,
            'deleted_at' => null,
        ]);
    }

    public function test_konfirmasi_berulang_idempoten_dan_memajukan_jatuh_tempo(): void
    {
        $jadwal = TransaksiBerulang::create([
            'nama' => 'Listrik PLN',
            'kategori' => 'Tagihan',
            'jumlah' => 480_000,
            'aturan' => 'bulanan',
            'jatuh_tempo' => today()->toDateString(),
            'metode' => 'BSI',
            'pencatat' => 'David NA',
            'aktif' => true,
        ]);

        $service = app(PengingatService::class);
        $occurrence = today()->toDateString();

        [$pertama, $dibuat] = $service->konfirmasi($jadwal, ['jatuh_tempo' => $occurrence], 'ui');
        $this->assertTrue($dibuat);
        $this->assertSame('ULANG-'.$jadwal->id.'-'.today()->format('Ymd'), $pertama->client_key);

        [$kedua, $dibuatLagi] = $service->konfirmasi($jadwal->fresh(), ['jatuh_tempo' => $occurrence], 'ui');
        $this->assertFalse($dibuatLagi);
        $this->assertSame($pertama->id, $kedua->id);
        $this->assertSame(1, \App\Models\Transaksi::query()->where('client_key', 'like', 'ULANG-%')->count());

        $jadwal->refresh();
        $this->assertTrue($jadwal->jatuh_tempo->greaterThan(today()));
        $this->assertTrue($jadwal->terakhir_dikonfirmasi->isToday());
    }

    public function test_kategori_dinormalisasi_dan_mutasi_tanpa_tujuan_ditolak(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'mutasi',
            'jumlah' => 100,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
        ]);
    }

    public function test_mutasi_tidak_boleh_berjenis_masuk(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'masuk',
            'kategori' => 'Mutasi',
            'jumlah' => 100,
            'pencatat' => 'David NA',
            'metode' => 'BSI',
            'kantong_tujuan' => 'Tunai',
        ]);
    }

    public function test_hapus_selalu_soft_delete_dan_riwayat_tercatat(): void
    {
        $transaksi = $this->transaksi->buat([
            'tanggal' => today()->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Makanan',
            'jumlah' => 25_000,
            'pencatat' => 'David NA',
        ]);

        $this->transaksi->hapus($transaksi, null, 'ui');

        $this->assertSoftDeleted('transaksi', ['id' => $transaksi->id]);
        $this->assertDatabaseHas('transaksi_riwayat', ['transaksi_id' => $transaksi->id, 'aksi' => 'dihapus']);
    }
}
