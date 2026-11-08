<?php

namespace Database\Seeders;

use App\Models\Anggaran;
use App\Models\Kantong;
use App\Models\KategoriMeta;
use App\Models\Transaksi;
use App\Models\TransaksiBerulang;
use App\Models\User;
use App\Services\TransaksiService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Transaksi::query()->exists()) {
            $this->command?->warn('Data transaksi sudah ada; seed demo dilewati.');

            return;
        }

        $david = User::updateOrCreate(
            ['email' => 'david@dompetkita.test'],
            ['name' => 'David NA', 'pencatat' => 'David NA', 'password' => Hash::make('password'), 'is_admin' => true]
        );

        $nurul = User::updateOrCreate(
            ['email' => 'nurul@dompetkita.test'],
            ['name' => 'Nurul Azizah', 'pencatat' => 'Nurul Azizah', 'password' => Hash::make('password'), 'is_admin' => true]
        );

        $mulai = today()->startOfMonth()->subMonths(5);
        $kantong = [
            ['nama' => 'Tunai', 'tipe' => 'harian', 'saldo_awal' => 1_250_000, 'tanggal_saldo_awal' => $mulai->toDateString(), 'urutan' => 1],
            ['nama' => 'BSI', 'tipe' => 'harian', 'saldo_awal' => 4_750_000, 'tanggal_saldo_awal' => $mulai->toDateString(), 'urutan' => 2],
            ['nama' => 'Shopee', 'tipe' => 'harian', 'saldo_awal' => 3_500_000, 'tanggal_saldo_awal' => $mulai->toDateString(), 'urutan' => 3],
        ];

        foreach ($kantong as $data) {
            Kantong::updateOrCreate(['nama' => $data['nama']], $data);
        }

        $meta = [
            'Makanan' => ['ikon' => 'utensils', 'warna' => '#A83B24', 'urutan' => 1],
            'Belanja' => ['ikon' => 'cart', 'warna' => '#4F6B4A', 'urutan' => 2],
            'Transportasi' => ['ikon' => 'car', 'warna' => '#496976', 'urutan' => 3],
            'Tagihan' => ['ikon' => 'bolt', 'warna' => '#87570F', 'urutan' => 4],
            'Pendidikan' => ['ikon' => 'book', 'warna' => '#6B7A8F', 'urutan' => 5],
            'Kesehatan' => ['ikon' => 'heart', 'warna' => '#8E5F66', 'urutan' => 6],
            'Rumah' => ['ikon' => 'home', 'warna' => '#8C6A4A', 'urutan' => 7],
            'Jajan' => ['ikon' => 'coffee', 'warna' => '#A76B4A', 'urutan' => 8],
            'Donasi' => ['ikon' => 'gift', 'warna' => '#7D7F5C', 'urutan' => 9],
            'Mutasi' => ['ikon' => 'repeat', 'warna' => '#87570F', 'urutan' => 10],
            'Gaji' => ['ikon' => 'bank', 'warna' => '#4F6B4A', 'urutan' => 11],
            'Proyek' => ['ikon' => 'sparkles', 'warna' => '#496976', 'urutan' => 12],
            'Hutang' => ['ikon' => 'arrow-down-left', 'warna' => '#8E5F66', 'urutan' => 13],
            'Piutang' => ['ikon' => 'arrow-up-right', 'warna' => '#657A5E', 'urutan' => 14],
        ];

        foreach ($meta as $kategori => $data) {
            KategoriMeta::updateOrCreate(['kategori' => $kategori], $data);
        }

        $service = app(TransaksiService::class);

        for ($bulanKe = 5; $bulanKe >= 0; $bulanKe--) {
            $bulan = today()->startOfMonth()->subMonths($bulanKe);
            $isBulanIni = $bulan->isSameMonth(today());
            $batasHari = $isBulanIni ? today()->day : $bulan->daysInMonth;
            $variasi = $bulanKe * 7_500;

            $this->pendapatan($service, $bulan, $batasHari, $variasi, $david, $nurul);

            $pengeluaran = [
                ['Makanan', 'Belanja bulanan di pasar', 1_250_000, 'BSI', 3, 'David NA'],
                ['Makanan', 'Sayur, telur, dan lauk', 185_000, 'Tunai', 7, 'Nurul Azizah'],
                ['Makanan', 'Belanja mingguan', 165_000, 'Tunai', 14, 'Nurul Azizah'],
                ['Makanan', 'Beras dan minyak', 210_000, 'BSI', 21, 'David NA'],
                ['Transportasi', 'Bensin motor', 120_000, 'Tunai', 6, 'David NA'],
                ['Transportasi', 'Ojek dan parkir', 65_000, 'Tunai', 12, 'Nurul Azizah'],
                ['Tagihan', 'Listrik rumah', 480_000, 'BSI', 5, 'David NA'],
                ['Tagihan', 'Internet rumah', 350_000, 'BSI', 10, 'David NA'],
                ['Tagihan', 'Air PDAM', 145_000, 'BSI', 20, 'Nurul Azizah'],
                ['Pendidikan', 'SPP sekolah', 600_000, 'BSI', 15, 'David NA'],
                ['Jajan', 'Kopi dan camilan', 75_000, 'Shopee', 9, 'Nurul Azizah'],
                ['Belanja', 'Belanja online', 280_000, 'Shopee', 18, 'Nurul Azizah'],
                ['Rumah', 'Sabun dan kebersihan', 95_000, 'Tunai', 11, 'Nurul Azizah'],
                ['Donasi', 'Donasi rutin', 100_000, 'Tunai', 21, 'David NA'],
                ['Kesehatan', 'Vitamin dan obat', 180_000, 'BSI', 23, 'Nurul Azizah'],
                ['Jajan', 'Makan di luar', 140_000, 'Shopee', 25, 'David NA'],
            ];

            foreach ($pengeluaran as $index => [$kategori, $keterangan, $jumlah, $metode, $hari, $pencatat]) {
                if ($isBulanIni) {
                    $hari = max(1, min($hari, $batasHari));
                } elseif ($hari > $batasHari) {
                    continue;
                }

                $service->buat([
                    'tanggal' => $bulan->copy()->day($hari)->toDateString(),
                    'jenis' => 'keluar',
                    'kategori' => $kategori,
                    'jumlah' => max(10_000, $jumlah + $variasi + (($index % 3) * 5_000)),
                    'keterangan' => $keterangan,
                    'pencatat' => $pencatat,
                    'metode' => $metode,
                    'sumber' => $index % 4 === 0 ? 'telegram' : 'ui',
                    'client_key' => $index % 4 === 0 ? 'DEMO-TG-'.$bulan->format('Ym').'-'.$index : null,
                ]);
            }

            $mutasi = [
                ['Tarik tunai untuk belanja dapur', 700_000, 'BSI', 'Tunai', 4, 'David NA'],
                ['Pindah saldo ke Shopee', 300_000, 'BSI', 'Shopee', 13, 'Nurul Azizah'],
                ['Top up dompet digital', 200_000, 'Shopee', 'Tunai', 24, 'Nurul Azizah'],
            ];

            foreach ($mutasi as $index => [$keterangan, $jumlah, $asal, $tujuan, $hari, $pencatat]) {
                if ($isBulanIni) {
                    $hari = max(1, min($hari, $batasHari));
                } elseif ($hari > $batasHari) {
                    continue;
                }

                $service->buat([
                    'tanggal' => $bulan->copy()->day($hari)->toDateString(),
                    'jenis' => 'keluar',
                    'kategori' => 'Mutasi',
                    'jumlah' => $jumlah,
                    'keterangan' => $keterangan,
                    'pencatat' => $pencatat,
                    'metode' => $asal,
                    'kantong_tujuan' => $tujuan,
                    'sumber' => 'ui',
                ]);
            }

            if ($isBulanIni) {
                $this->transaksiTerbaru($service, $david, $nurul);
            }
        }

        $contoh = Transaksi::query()->where('kategori', 'Makanan')->latest('tanggal')->first();
        if ($contoh !== null) {
            $service->perbarui($contoh, [
                'keterangan' => 'Belanja mingguan di pasar (dikoreksi)',
            ], $nurul, 'ui');
        }

        $sekali = [
            [5, 'Transportasi', 'Servis motor dan ganti oli', 850_000, 'BSI', 19, 'David NA'],
            [4, 'Rumah', 'Perbaikan atap bocor', 1_200_000, 'BSI', 22, 'David NA'],
            [3, 'Belanja', 'Hadiah ulang tahun keluarga', 350_000, 'Shopee', 16, 'Nurul Azizah'],
            [2, 'Kesehatan', 'Kontrol gigi', 600_000, 'BSI', 8, 'Nurul Azizah'],
            [1, 'Pendidikan', 'Buku dan alat sekolah', 450_000, 'BSI', 12, 'Nurul Azizah'],
        ];

        foreach ($sekali as [$mundur, $kategori, $keterangan, $jumlah, $metode, $hari, $pencatat]) {
            $bulan = today()->startOfMonth()->subMonths($mundur);
            $service->buat([
                'tanggal' => $bulan->copy()->day($hari)->toDateString(),
                'jenis' => 'keluar',
                'kategori' => $kategori,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
                'pencatat' => $pencatat,
                'metode' => $metode,
                'sumber' => 'ui',
            ]);
        }

        $dihapus = $service->buat([
            'tanggal' => today()->subDays(3)->toDateString(),
            'jenis' => 'keluar',
            'kategori' => 'Jajan',
            'jumlah' => 45_000,
            'keterangan' => 'Catatan dobel, dihapus',
            'pencatat' => 'David NA',
            'metode' => 'Tunai',
            'sumber' => 'ui',
        ]);
        $service->hapus($dihapus, $david, 'ui');

        $this->anggaran();
        $this->jadwalBerulang($david, $nurul);
    }

    private function pendapatan(
        TransaksiService $service,
        Carbon $bulan,
        int $batasHari,
        int $variasi,
        User $david,
        User $nurul
    ): void {
        $pendapatan = [
            ['Gaji', 'Gaji bulanan David', 9_500_000, 'BSI', 25, 'David NA'],
            ['Gaji', 'Honor mengajar Nurul', 2_750_000, 'BSI', 2, 'Nurul Azizah'],
            ['Proyek', 'Proyek desain lepas', 1_250_000 + $variasi, 'BSI', 1, 'Nurul Azizah'],
        ];

        foreach ($pendapatan as $index => [$kategori, $keterangan, $jumlah, $metode, $hari, $pencatat]) {
            if ($hari > $batasHari) {
                continue;
            }

            $service->buat([
                'tanggal' => $bulan->copy()->day($hari)->toDateString(),
                'jenis' => 'masuk',
                'kategori' => $kategori,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
                'pencatat' => $pencatat,
                'metode' => $metode,
                'sumber' => 'telegram',
                'client_key' => 'DEMO-MASUK-'.$bulan->format('Ym').'-'.$index,
            ]);
        }
    }

    private function transaksiTerbaru(TransaksiService $service, User $david, User $nurul): void
    {
        $daftar = [
            ['Makanan', 'Sarapan dan kopi', 48_000, 'Tunai', 'Nurul Azizah', 0],
            ['Transportasi', 'Parkir dan bensin', 55_000, 'Tunai', 'David NA', 1],
            ['Jajan', 'Camilan anak', 32_000, 'Shopee', 'Nurul Azizah', 1],
            ['Rumah', 'Gas dan air minum', 87_000, 'Tunai', 'David NA', 2],
            ['Makanan', 'Lauk untuk makan malam', 76_000, 'Tunai', 'Nurul Azizah', 3],
        ];

        foreach ($daftar as $index => [$kategori, $keterangan, $jumlah, $metode, $pencatat, $mundur]) {
            $tanggal = today()->subDays($mundur);
            if ($tanggal->month !== today()->month) {
                continue;
            }

            $service->buat([
                'tanggal' => $tanggal->toDateString(),
                'jenis' => 'keluar',
                'kategori' => $kategori,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
                'pencatat' => $pencatat,
                'metode' => $metode,
                'sumber' => 'ui',
            ]);
        }
    }

    private function anggaran(): void
    {
        $total = 7_500_000;
        $kategori = [
            'Makanan' => 2_200_000,
            'Belanja' => 1_400_000,
            'Tagihan' => 1_200_000,
            'Transportasi' => 700_000,
            'Pendidikan' => 700_000,
            'Kesehatan' => 500_000,
            'Jajan' => 300_000,
            'Rumah' => 400_000,
        ];

        for ($i = 3; $i >= 0; $i--) {
            $periode = today()->startOfMonth()->subMonths($i)->format('Y-m');

            Anggaran::updateOrCreate(
                ['periode' => $periode, 'kategori' => null],
                ['jumlah' => $total, 'ambang' => 80]
            );

            foreach ($kategori as $nama => $jumlah) {
                Anggaran::updateOrCreate(
                    ['periode' => $periode, 'kategori' => $nama],
                    ['jumlah' => $jumlah, 'ambang' => $nama === 'Makanan' ? 75 : 80]
                );
            }
        }
    }

    private function jadwalBerulang(User $david, User $nurul): void
    {
        $jatuhTempo = [
            ['Listrik PLN', 'Tagihan', 480_000, 'bulanan', today()->addDays(30), 'BSI', 'David NA'],
            ['Internet rumah', 'Tagihan', 350_000, 'bulanan', today()->addDays(33), 'BSI', 'David NA'],
            ['SPP sekolah', 'Pendidikan', 600_000, 'bulanan', today()->addDays(35), 'BSI', 'Nurul Azizah'],
            ['Air PDAM', 'Tagihan', 145_000, 'bulanan', today()->addDays(38), 'BSI', 'Nurul Azizah'],
            ['Iuran sampah', 'Rumah', 75_000, 'bulanan', today(), 'Tunai', 'David NA'],
            ['Iuran lingkungan', 'Rumah', 50_000, 'bulanan', today()->addDays(2), 'Tunai', 'Nurul Azizah'],
        ];

        foreach ($jatuhTempo as [$nama, $kategori, $jumlah, $aturan, $tanggal, $metode, $pencatat]) {
            TransaksiBerulang::updateOrCreate(
                ['nama' => $nama],
                [
                    'kategori' => $kategori,
                    'jumlah' => $jumlah,
                    'aturan' => $aturan,
                    'jatuh_tempo' => $tanggal->toDateString(),
                    'metode' => $metode,
                    'pencatat' => $pencatat,
                    'aktif' => true,
                ]
            );
        }
    }
}
