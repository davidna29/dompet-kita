# Aplikasi Keuangan Keluarga (Hermes + Hostinger) — Product Requirements Document

**Status:** Draft, menunggu persetujuan Hermes (STB)
**Versi:** 0.3
**Owner:** TBD
**Terakhir diperbarui:** 2026-11-02
**Kesiapan:** Implementation-ready Tahap 1 (Bagian 18 disetujui Hermes 2026-11-02)

Legenda label: **[Konfirmasi]** = berasal dari dokumen "Pola Sinkron" atau pernyataan pengguna. **[Asumsi]** = asumsi penyusun, mohon dikoreksi. **[Usulan]** = rekomendasi penyusun, perlu keputusan.

> **Untuk Hermes (STB):** mohon baca Bagian 9 (data), Bagian 7 (FR-007 API), Bagian 7A/7B (fitur dan perubahan skema), dan **Bagian 18 (checklist persetujuan)**. Beri keputusan setuju/tolak/usulan perubahan per butir di Bagian 18. Larangan di Bagian 7 "Pola Sinkron" tetap dipatuhi seluruhnya.

## 0. Keputusan pengguna sampai saat ini

| Keputusan | Isi |
|---|---|
| Cakupan | Semua fitur Must dan semua fitur Should masuk cakupan (FR-001 sampai FR-020); Could ditunda |
| Tahap rilis | Tahap 1 = semua Must, lalu deploy; Tahap 2 = semua Should |
| Saldo per kantong | Diperlukan; kantong awal Tunai, BSI, Shopee; tambah kolom `kantong_tujuan` (menunggu persetujuan Hermes) |
| Selisih saldo | Pilihan C: hanya ditampilkan, tidak dicatat otomatis |
| Foto struk | Ditunda karena memberatkan hosting |
| Platform | Mobile-first (keluarga selalu membuka lewat HP) |
| Tech stack | Laravel 13, PHP 8.5 (cadangan 8.4, minimum 8.3), MySQL |
| Hosting | Hostinger shared hosting, sudah ada SSH |
| Pembangun | Pengguna dibantu agen opencode; kode di GitHub; deploy oleh pengguna setelah Must selesai |

## 1. Ringkasan eksekutif

Keluarga saat ini mencatat keuangan lewat agen Hermes di STB melalui Telegram, dengan ledger berupa file CSV (19 baris per 2026-11-02). Produk ini menambahkan **aplikasi web mobile-first** agar data keuangan bisa dilihat dan diedit lewat UI, sementara agen Hermes tetap menjadi pencatat lewat chat dan penyinkron data. Hostinger menjadi sumber utama data (MySQL + API + UI); STB menjadi admin sinkron dan menyimpan cache/cadangan CSV.

Nilai yang diharapkan: satu sumber data yang konsisten, bisa diedit lewat dua jalur (chat dan app) tanpa data ganda atau hilang, laporan Hermes selalu memakai data terbaru, serta fitur sehari-hari seperti saldo per kantong, anggaran, dan pengingat tagihan.

## 2. Latar belakang dan masalah

**Kondisi saat ini [Konfirmasi]**
- Pencatatan lewat Telegram ke agen Hermes di STB; ledger di `/root/keuangan-keluarga/transaksi.csv`.
- Laporan berkala Hermes: harian 21:30, mingguan Minggu 19:00, bulanan tanggal 1 pukul 09:00 WIB.

**Masalah [Asumsi, belum ada bukti riset]**
- Mengoreksi atau mencari transaksi lewat chat kurang praktis.
- Tidak ada tampilan visual yang bisa dibuka kapan saja oleh David dan Nurul.
- CSV lokal di STB tidak bisa diakses langsung oleh dua orang dari perangkat masing-masing.

**Dampak bila tidak dikerjakan:** koreksi data tetap bergantung pada chat/terminal STB.

## 3. Tujuan dan non-tujuan

**Tujuan**
- G1. David dan Nurul dapat melihat, menambah, mengedit, dan menghapus (soft) transaksi lewat UI di HP.
- G2. Pencatatan lewat Telegram tetap berjalan dan masuk ke database yang sama tanpa duplikat.
- G3. Data di UI dan data yang dipakai Hermes untuk laporan konsisten.
- G4. Aturan nilai di Bagian 9 (terutama pengecualian kategori Mutasi) berlaku di UI dan laporan.
- G5. Keluarga mendapat saldo per kantong, anggaran, dan pengingat tagihan.

**Non-tujuan**
- Aplikasi mobile native, mode offline, sinkron real-time (websocket/long-poll).
- Multi-mata-uang, integrasi otomatis ke rekening bank, dan lampiran foto struk (ditunda karena beban hosting).
- Multi-keluarga atau pendaftaran pengguna umum.

## 4. Pengguna dan peran

| Pengguna | Kebutuhan | Izin |
|---|---|---|
| David NA [Konfirmasi] | Catat via chat, cek/koreksi via UI di HP | Lihat, tambah, edit, hapus (soft) semua transaksi |
| Nurul Azizah [Konfirmasi] | Sama seperti David | Sama seperti David |
| Agen Hermes (STB) [Konfirmasi] | Mendorong catatan Telegram, menarik data, mengirim pengingat | Token admin terpisah via API |

**Akun UI [Usulan, belum dikonfirmasi pengguna]:** dua akun terpisah (David, Nurul) agar `pencatat` entri UI terisi otomatis. Alternatif: satu akun keluarga bersama.

## 5. Cakupan dan tahap rilis

**Tahap 1: Must (FR-001 sampai FR-010)**
Login UI, daftar transaksi dengan filter, tambah/edit/hapus (soft), API untuk STB, ringkasan periode (Mutasi dikecualikan dan ditampilkan terpisah), migrasi 19 baris, sinkron STB, penyelesaian konflik.

**Tahap 2: Should (FR-011 sampai FR-020)**
Ekspor CSV, pull sesuai permintaan, dashboard grafik, anggaran per kategori, transaksi berulang dan pengingat tagihan, ikon/warna kategori, riwayat perubahan, peringatan dan ringkasan pintar lewat Hermes, PWA + mode gelap, saldo per kantong.

**Could / nanti:** target tabungan (FR-021), hutang dan piutang (FR-023), kalender, pencarian teks, tag (FR-024), laporan PDF/Excel (FR-025).

**Won't (saat ini):** lampiran foto struk (FR-022), hapus permanen dari UI, offline-first, websocket, penambahan nilai `jenis` baru.

## 6. Alur utama

**A. Catat via Telegram**
1. Pengirim mengirim laporan ke Hermes.
2. Hermes mem-parse (tanggal tanpa tahun = tahun berjalan; tanpa tanggal = hari ini WIB; `pencatat` dari identitas chat).
3. Hermes `POST /api/transaksi` dengan `sumber=telegram` dan `client_key=TELEGRAM-<chatid>-<msgid>`.
4. Jika `client_key` sudah ada, API mengembalikan baris lama tanpa insert baru. Hermes memperbarui CSV lokal.

**B. Catat/edit via UI**
1. Pengguna login, mengisi form, simpan.
2. UI menulis langsung ke database dengan `sumber=ui`.
3. Data baru sampai ke CSV STB pada pull berikutnya.

**C. Edit/hapus via chat Hermes**
1. Pengguna meminta koreksi lewat chat (mis. "ubah transaksi kemarin jadi 40000").
2. Hermes mengidentifikasi baris (id), lalu `PUT` atau `DELETE` (soft).
3. Bila kandidat baris lebih dari satu, Hermes bertanya dulu **[Usulan]**.

**D. Laporan berkala**
Sebelum laporan terjadwal, Hermes `GET /api/transaksi?since=<terakhir>`, memperbarui CSV (menandai baris soft-deleted), lalu menyusun laporan dengan Mutasi dikecualikan dari total belanja.

**Jalur gagal**
- API tidak terjangkau saat Telegram masuk: Hermes menyimpan antrean lokal dan mengirim ulang (aman karena idempoten) **[Usulan]**.
- Pull gagal sebelum laporan: laporan tetap dikirim dengan penanda "data mungkin belum terbaru" **[Usulan]**.

## 7. Persyaratan fungsional

### Tahap 1: Must

**FR-001 Login UI.** Akses UI hanya untuk pengguna terautentikasi. Given kredensial benar, When login, Then masuk ke daftar transaksi. Given 5 percobaan dalam 1 menit dari satu IP, When percobaan ke-6, Then ditolak sementara [Konfirmasi: rate-limit 5x/menit/IP]. Semua akses lewat HTTPS. Token UI terpisah dari token admin STB.

**FR-002 Daftar transaksi.** Menampilkan transaksi tidak terhapus, urut tanggal terbaru, dengan filter rentang tanggal, jenis, kategori, pencatat. Baris dengan `deleted_at` terisi tidak muncul. Jumlah tampil berformat rupiah (mis. Rp35.000) walau tersimpan angka polos. Daftar kosong menampilkan keadaan kosong dengan ajakan menambah transaksi.

**FR-003 Tambah transaksi via UI.** Kolom: tanggal, jenis, kategori, jumlah, keterangan (opsional), metode (opsional), dan `kantong_tujuan` (hanya untuk Mutasi); `pencatat` dan `sumber=ui` terisi otomatis. Jumlah harus bilangan bulat positif; "35.000" atau "35rb" dinormalisasi atau ditolak dengan pesan jelas, dan yang tersimpan hanya digit polos. Tanggal default hari ini (WIB). Kategori bebas, maksimum 50 karakter.

**FR-004 Edit transaksi.** Semua kolom boleh diedit mengikuti aturan Bagian 9; `updated_at` diset server saat simpan. Jenis tidak bisa diisi nilai selain masuk/keluar.

**FR-005 Hapus transaksi (soft delete).** Tombol hapus meminta konfirmasi; hasilnya mengisi `deleted_at`, tidak ada DELETE fisik; baris tetap muncul di `GET /api/transaksi?since=` agar STB dapat menyinkronkan penghapusan.

**FR-006 Ringkasan periode.** Total pemasukan, total pengeluaran, selisih untuk hari/minggu/bulan/rentang bebas. Total pengeluaran **tidak** menyertakan kategori Mutasi; Mutasi ditampilkan terpisah bila ada. Angka UI sama dengan laporan Hermes untuk periode dan data yang sama.

**FR-007 API untuk STB** [Konfirmasi dari Pola Sinkron; tambahan ada di Bagian 18]
- Auth `Authorization: Bearer <ADMIN_TOKEN>`; token acak minimal 32 karakter.
- `GET /api/health`, `GET /api/transaksi?since=&limit=&offset=` (termasuk soft-deleted), `POST /api/transaksi` (idempoten via `client_key`), `PUT /api/transaksi/{id}`, `DELETE /api/transaksi/{id}` (soft).
- Semua respons JSON; error berupa status HTTP + `{error: pesan singkat}`.
- Format `since` dan `server_time`: `YYYY-MM-DDTHH:MM:SS` zona Asia/Jakarta, tanpa offset.
- Validasi gagal (Mutasi tanpa `kantong_tujuan`, `jenis` selain masuk/keluar, `jumlah` ≤ 0) → 422 + `{error: pesan singkat Bahasa Indonesia}`.
- Kriteria: POST dengan `client_key` yang sama dua kali menghasilkan satu baris, dan respons kedua mengembalikan baris yang sama.

**FR-008 Sinkron ke STB.** Push langsung dari Telegram; pull wajib sebelum laporan harian 21:30, mingguan Minggu 19:00, bulanan tanggal 1 pukul 09:00 WIB [Konfirmasi]. Transaksi yang dibuat/diedit/dihapus di UI sebelum waktu laporan tercermin di laporan tersebut. CSV cache lokal STB memakai header `tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan` (kolom ke-8 opsional, kosong bila bukan Mutasi).

**FR-009 Migrasi awal.** Impor 19 baris CSV apa adanya: kolom kosong jadi NULL/kosong, `sumber=telegram`, `client_key=MIGRASI-<nomor>` [Konfirmasi]. Jumlah baris di database = 19, total per jenis dan per kategori sama dengan CSV. Hanya setelah `GET /api/transaksi` terbaca dari STB, CSV lokal berubah peran menjadi cache + cadangan. Dijalankan sekali. Sebelum migrasi, pengguna mengisi `saldo_awal` + `tanggal_saldo_awal` tiap kantong (Tunai, BSI, Shopee) dari angka nyata; tanpa ini saldo kantong tidak dihitung.

**FR-010 Penyelesaian konflik.** Last-write-wins berdasarkan `updated_at` [Konfirmasi]. `updated_at` ditetapkan server Hostinger, bukan jam klien [Usulan].

### Tahap 2: Should

**FR-011 Ekspor CSV.** Pengguna mengunduh transaksi sesuai filter aktif sebagai CSV (jumlah tetap digit polos).

**FR-012 Pull sesuai permintaan [Usulan].** Sebelum Hermes menjawab pertanyaan total/saldo di chat, Hermes melakukan pull ringan agar jawaban sesuai data UI terkini.

**FR-013 Dashboard beranda.** Untuk bulan berjalan: total pemasukan, total pengeluaran (tanpa Mutasi), selisih, perbandingan dengan bulan lalu, 5 kategori terbesar, pengeluaran per pencatat. Grafik: donat pengeluaran per kategori, batang tren 6 bulan, garis kumulatif harian. Angka sama dengan FR-006 dan laporan Hermes; Mutasi tidak masuk grafik kategori.

**FR-014 Anggaran per kategori.** Anggaran bulanan per kategori dan/atau total; progres (terpakai, sisa, persen). Given anggaran Makanan Rp1.000.000 dan pengeluaran Makanan bulan ini Rp800.000, Then progres 80% dan ditandai "hampir habis". Ambang peringatan default 80% dan 100% [Usulan]. Mutasi dan pemasukan tidak dihitung. Bulan baru memakai anggaran bulan sebelumnya kecuali diubah.

**FR-015 Transaksi berulang dan pengingat tagihan.** Jadwal berisi nama, kategori, jumlah, aturan ulang, metode, pencatat default. Transaksi **tidak dibuat otomatis tanpa konfirmasi**: pada hari jatuh tempo Hermes mengirim pengingat; pengguna konfirmasi satu ketukan di UI atau balasan singkat di chat, dan baru saat itu transaksi dibuat dengan `sumber` sesuai tempat konfirmasi (`ui` atau `telegram`). Konfirmasi dua kali untuk jatuh tempo yang sama tidak membuat baris ganda (`client_key` = `ULANG-<id jadwal>-<YYYYMMDD>` [Usulan]). Jumlah boleh diubah saat konfirmasi. Penjadwalan memakai cron Hermes di STB. Jadwal dihapus secara soft.

**FR-016 Ikon, warna, urutan kategori.** Tabel `kategori_meta`; `transaksi.kategori` tetap teks bebas. Kategori tanpa meta tampil dengan ikon dan warna default; kategori baru dari chat tetap diterima. Mutasi punya penanda visual khusus.

**FR-017 Riwayat perubahan.** Setiap edit/hapus menyimpan waktu, siapa (David NA, Nurul Azizah, atau Hermes), sumber, nilai sebelum dan sesudah. UI menampilkan "terakhir diubah oleh X pada Y" dan daftar riwayat.

**FR-018 Peringatan dan ringkasan pintar lewat Hermes.** Hermes mengirim di Telegram: peringatan anggaran 80%/100%, pengingat tagihan, ringkasan minggu ini dibanding minggu lalu. Peringatan anggaran terkirim paling banyak sekali per ambang per kategori per bulan; angka mengecualikan Mutasi; Hermes menarik data terbaru sebelum menghitung.

**FR-019 PWA dan mode gelap.** UI bisa dipasang di layar utama HP dan tampil seperti aplikasi, dengan mode terang/gelap. **Tetap online-only:** tidak ada pencatatan offline (Pola Sinkron melarang UI offline-first tanpa antrean sinkron); tanpa koneksi, UI menampilkan galat yang jelas dan tidak menyimpan data lokal.

**FR-020 Saldo per kantong.** Lihat Bagian 7B.

### Could (ditunda)
- **FR-021 Target tabungan:** nama, nominal, tenggat, progres.
- **FR-023 Hutang dan piutang:** pihak, nominal, sisa, jatuh tempo; tidak mengubah `jenis`.
- **FR-024 Kalender, pencarian teks, tag:** tag lewat tabel relasi, bukan kolom baru di `transaksi`.
- **FR-025 Laporan bulanan PDF/Excel:** memperluas FR-011.
- **FR-022 Lampiran foto struk:** Won't saat ini (memberatkan hosting).

## 7A. Ringkasan fitur lanjutan dan dampak ke sisi STB

| ID | Fitur | Tahap | Tabel/endpoint baru | Dampak ke sisi STB |
|---|---|---|---|---|
| FR-011 | Ekspor CSV | 2 | Tidak ada | Tidak ada |
| FR-012 | Pull sesuai permintaan | 2 | Tidak ada | Hermes menarik data sebelum menjawab |
| FR-013 | Dashboard | 2 | Tidak ada | Tidak ada |
| FR-014 | Anggaran | 2 | `anggaran` | Hermes membaca anggaran untuk peringatan |
| FR-015 | Transaksi berulang + pengingat | 2 | `transaksi_berulang` | Hermes menjalankan pengingat terjadwal |
| FR-016 | Ikon/warna kategori | 2 | `kategori_meta` | Tidak ada |
| FR-017 | Riwayat perubahan | 2 (dicatat sejak 1) | `transaksi_riwayat` | Tidak ada |
| FR-018 | Peringatan + ringkasan pintar | 2 | Memakai FR-014, FR-015 | Pesan Telegram tambahan |
| FR-019 | PWA + mode gelap | 2 | Tidak ada | Tidak ada |
| FR-020 | Saldo per kantong | 2 (kolom sejak 1) | `kantong`, kolom `kantong_tujuan` | Perlu persetujuan skema |

Tabel `transaksi` dan aturan nilai di Bagian 9 tidak berubah, kecuali satu kolom opsional `kantong_tujuan` (Bagian 7B). Tabel tambahan memakai konvensi yang sama (jumlah INT digit polos, WIB, soft delete).

## 7B. Saldo per kantong (FR-020)

Skema sekarang belum cukup karena Mutasi hanya mencatat asal lewat `metode` dan tidak mencatat tujuan. Rancangan di bawah **[Usulan]** dan menyentuh skema wajib di Pola Sinkron, sehingga perlu persetujuan Hermes.

- **Kantong** = nilai `metode`. Kantong awal: **Tunai, BSI, Shopee** [Konfirmasi pengguna]. Tabel `kantong`: id, nama unik, `saldo_awal` INT, `tanggal_saldo_awal` DATE, urutan, `deleted_at`.
- **Perubahan skema:** satu kolom `kantong_tujuan` VARCHAR(30) NULL pada `transaksi`. Wajib terisi bila `kategori=Mutasi`, kosong bila bukan Mutasi. Kolom lama tidak berubah, `jenis` tetap masuk/keluar, klien lama yang tidak mengirim kolom ini tetap berfungsi. Disarankan ada sejak migrasi pertama (Tahap 1).
- **Aturan Mutasi:** `metode` (asal) dan `kantong_tujuan` sama-sama wajib dan berbeda. Contoh chat "tarik tunai BSI ke Tunai 500rb" menjadi `jenis=keluar`, `kategori=Mutasi`, `metode=BSI`, `kantong_tujuan=Tunai`, `jumlah=500000`.
- **Rumus saldo kantong k**, dihitung server dari transaksi tidak terhapus pada atau setelah `tanggal_saldo_awal`:

```
saldo_k = saldo_awal_k + masuk(metode = k) - keluar(metode = k, termasuk Mutasi) + Mutasi(kantong_tujuan = k)
```

- **Dua hitungan berbeda:** total belanja tetap mengecualikan Mutasi (Bagian 9), sedangkan saldo kantong memasukkan Mutasi. UI dan laporan memberi label jelas.
- **Kriteria:** Given saldo awal BSI Rp1.000.000, pemasukan BSI Rp500.000, pengeluaran BSI Rp200.000, Mutasi BSI ke Tunai Rp300.000, Then BSI = Rp1.000.000 dan Tunai bertambah Rp300.000; total belanja contoh yang sama hanya Rp200.000.
- **Migrasi 19 baris:** baris lama mungkin tanpa `metode`. Saldo awal diisi dari saldo nyata per kantong pada satu tanggal; transaksi sebelum tanggal itu tidak dihitung. Transaksi baru tanpa `metode` ditandai "kantong belum dipilih" dan tidak masuk saldo mana pun.
- **Hermes:** laporan berkala memuat saldo per kantong dari `GET /api/kantong` (dihitung server, bukan dari CSV).
- **Rekonsiliasi (pilihan C, keputusan pengguna):** "cocokkan saldo" hanya menampilkan selisih antara saldo sistem dan saldo nyata yang dimasukkan; selisih tidak dicatat otomatis, pengguna menambahkan sendiri transaksi yang terlewat. Koreksi dengan kategori khusus ditunda.

## 7C. Tech stack dan arsitektur

Fakta versi berasal dari sumber di akhir bagian (dibaca 2026-11-02; cek ulang saat mulai). Butir lain **[Usulan]**.

| Lapisan | Pilihan | Catatan |
|---|---|---|
| Framework | Laravel 13, satu aplikasi monolit (UI + API) | Rilis Maret 2026; tidak ada rilis LTS, rencanakan naik versi mayor tiap tahun |
| PHP | Target 8.5 (disetujui), minimum 8.3, cadangan 8.4 | Laravel 13 mendukung PHP 8.3 sampai 8.5; Laravel 14 diumumkan butuh PHP 8.4 |
| Database | MySQL, utf8mb4 | Sesuai Pola Sinkron; Laravel migrations |
| Model data | Eloquent dengan `SoftDeletes` | Cocok dengan `deleted_at`; `GET since` memakai `withTrashed()` |
| UI | Blade + Livewire + Tailwind, mobile-first | Satu bahasa (PHP), tanpa Node di server |
| Grafik | Chart.js (dibundel lewat build lokal) | Dashboard FR-013 |
| PWA | Manifest + service worker minimal | Hanya cache aset statis, bukan data |
| Auth UI | Sesi bawaan Laravel, throttle login 5/menit/IP | Dua akun David dan Nurul |
| Auth API STB | Middleware Bearer token terpisah | Bandingkan dengan `hash_equals`; token ≥32 karakter di `.env` |
| API | Rute `/api/*` + API Resource | Nama field persis Bagian 9; bentuk amplop list perlu disepakati (Bagian 18) |
| Idempotensi | Unique index `client_key` | POST kunci sama mengembalikan baris lama |
| Zona waktu | `APP_TIMEZONE=Asia/Jakarta`; `updated_at` diset server | Mendukung last-write-wins |
| Cache, sesi, antrean | Driver `database`/`file`, antrean `sync` | Tanpa Redis dan tanpa worker permanen |
| Pengujian | Pest atau PHPUnit | Kontrak API, aturan Mutasi, idempotensi, soft delete, rumus saldo |
| Pengembangan | opencode + GitHub | Lihat Bagian 19 |

**Batasan hosting**
- Hosting pengguna: Hostinger shared hosting, **SSH sudah aktif** [Konfirmasi]. Composer dan versi PHP di SSH tetap dicek saat setup.
- Di Hostinger, PHP 8.5 bisa dipilih di hPanel, tetapi PHP di SSH bisa tertinggal di versi lama sehingga `composer install` gagal; perbaikannya mengarahkan `PATH` ke biner PHP terpilih (mis. `/opt/alt/php85/usr/bin`).
- Rilis PHP baru kadang mendahului dukungan paket; bila ada galat dependensi, turun ke PHP 8.4.
- Tanpa unggahan foto struk, beban penyimpanan dan I/O hosting tetap ringan.

Sumber: Laravel News "Laravel Release Cycle" (https://laravel-news.com/laravel-releases); benjamincrozat.com/laravel-versions; dev.to/itxshakil "Deploy Laravel 13 on shared hosting"; dev.to/joassanon "PHP version mismatch on Hostinger shared hosting"; packagist.org/packages/thetamari/laravel-hostinger.

## 8. Persyaratan non-fungsional

- **Keamanan [Konfirmasi]:** HTTPS wajib untuk API dan UI; token admin terpisah dari token user; token bocor diganti lewat terminal STB. Token tidak boleh ada di PRD, repo, atau chat.
- **Privasi:** data keuangan pribadi; akses hanya dua pengguna dan agen STB. Log akses tidak menyimpan isi keterangan transaksi [Usulan].
- **Kompatibilitas:** berjalan di shared hosting PHP/MySQL tanpa proses persisten [Konfirmasi]; STB tidak wajib menjalankan browser atau websocket [Konfirmasi].
- **Mobile-first [Konfirmasi]:** keluarga selalu membuka lewat HP; semua layar dirancang untuk ponsel lebih dulu (Bagian 10). Antarmuka Bahasa Indonesia; kontras dan ukuran sentuh memadai.
- **Kinerja [Usulan, TBD]:** daftar transaksi dimuat dalam waktu wajar di HP dengan koneksi seluler biasa; target angka ditetapkan setelah ada data nyata.
- **Cadangan:** salinan CSV harian di STB (`~/.hermes/cron/output/`) diatur sisi STB [Konfirmasi]. Cadangan database Hostinger dikonfirmasi tersedia di paket yang dipakai [Usulan].
- **Zona waktu:** Asia/Jakarta untuk semua tanggal dan timestamp [Konfirmasi].

## 9. Data dan aturan nilai

Tabel `transaksi` (MySQL, utf8mb4) [Konfirmasi; satu kolom tambahan diusulkan]:

| Kolom | Tipe | Aturan |
|---|---|---|
| id | INT AUTO_INCREMENT PK | Stabil, tidak dipakai ulang |
| tanggal | DATE | YYYY-MM-DD, WIB |
| jenis | ENUM('masuk','keluar') | Hanya dua nilai, huruf kecil |
| kategori | VARCHAR(50) | Bebas; 'Mutasi' khusus (pindah kantong, bukan belanja). Normalisasi server: trim spasi, huruf pertama kapital sisanya kecil |
| jumlah | INT | Digit polos, bulat positif |
| keterangan | TEXT | Boleh kosong |
| pencatat | VARCHAR(50) | 'David NA' / 'Nurul Azizah'; dari UI dipetakan dari akun login, bukan ketikan bebas |
| metode | VARCHAR(30) | Boleh kosong (Tunai, BSI, Shopee, dst.); untuk Mutasi = kantong asal |
| sumber | VARCHAR(20) | 'telegram' / 'ui', wajib |
| client_key | VARCHAR(64) UNIQUE | Boleh null; kunci idempotensi |
| created_at, updated_at | DATETIME | WIB |
| deleted_at | DATETIME null | Soft delete |
| **kantong_tujuan** (usulan baru) | VARCHAR(30) NULL | Wajib untuk Mutasi, kosong selain itu |

**Aturan yang tidak boleh diubah [Konfirmasi]**
1. `jenis` hanya masuk/keluar; Mutasi = `jenis=keluar` + `kategori=Mutasi`.
2. Semua total belanja/pengeluaran mengecualikan Mutasi; Mutasi ditampilkan terpisah.
3. `jumlah` selalu bilangan bulat positif; tanda ditentukan `jenis`.
4. Tanggal Telegram tanpa tahun = tahun berjalan; tanpa tanggal = hari ini (WIB).
5. `pencatat` default dari pengirim: chat David = David NA, chat Nurul = Nurul Azizah.
6. Penghapusan selalu soft delete; STB menandai baris CSV terhapus dan tidak membuang baris fisik sebelum backup bulanan.

**Pemetaan nama field:** UI dan API memakai nama field persis seperti tabel di atas; label tampilan berbeda hanya di UI.

**Retensi:** transaksi tidak dihapus permanen dari UI. Pembersihan baris soft-deleted lama: TBD.

## 10. UX/UI mobile-first

Layar utama: Login, Daftar transaksi + filter + tombol tambah, Form tambah/edit, Ringkasan/Dashboard, Kantong, Pengaturan (anggaran, jadwal berulang, kategori, ekspor).

Keadaan yang wajib ada: memuat, kosong, galat jaringan/API, sukses simpan, konfirmasi hapus. Transaksi Mutasi ditandai visual agar tidak tertukar dengan belanja. Desain visual belum ada.

**Prinsip mobile-first** (keputusan pengguna; rincian **[Usulan]**):
- Rancang untuk layar ponsel sekitar 360 px lebih dulu; layar besar hanya memperluas tata letak.
- Navigasi bawah dalam jangkauan ibu jari: Beranda, Transaksi, Tambah, Ringkasan, Kantong. Tombol tambah selalu terlihat.
- Tambah transaksi dengan sedikit ketukan: papan angka dengan pemisah ribuan otomatis (tersimpan digit polos), tanggal default hari ini, kategori dan kantong lewat pilihan cepat yang paling sering dipakai.
- Daftar transaksi berbentuk baris/kartu vertikal, bukan tabel lebar; filter di panel yang bisa dibuka; edit/hapus dengan konfirmasi.
- Target sentuh minimal sekitar 44 px, teks terbaca tanpa zoom, tidak ada scroll horizontal.
- Grafik sederhana (donat dan batang) agar terbaca di layar kecil.
- Halaman ringan untuk koneksi seluler; online-only dengan opsi dipasang di layar utama (FR-019).
- Diuji di HP yang benar-benar dipakai keluarga sebelum rilis.

## 11. Integrasi dan ketergantungan

| Integrasi | Tujuan | Data | Otorisasi | Perilaku gagal | Pemilik |
|---|---|---|---|---|---|
| STB Hermes ↔ API Hostinger | Push/pull transaksi, pengingat | Baris transaksi JSON | Bearer ADMIN_TOKEN | Antrean + kirim ulang idempoten; laporan bertanda "mungkin belum terbaru" [Usulan] | Sisi STB |
| Telegram → STB | Menerima laporan | Pesan teks | Di luar cakupan PRD | Di luar cakupan | Sisi STB |
| Hostinger (PHP/MySQL) | Hosting UI, API, DB | Semua data | Akun hosting | Pemulihan dari backup | Pengguna |
| GitHub | Penyimpanan kode, pull request | Kode (tanpa rahasia) | Akun pengguna | — | Pengguna |

## 12. Metrik keberhasilan

Tanpa baseline atau target dari pengguna; semuanya **[Usulan, TBD]**:
- **Konsistensi data:** laporan berkala yang totalnya sama dengan total UI / total laporan. Target: TBD.
- **Duplikasi:** transaksi ganda per bulan. Target usulan: 0.
- **Pemakaian:** transaksi per bulan menurut `sumber`. Tanpa target.
- **Koreksi:** jumlah edit per bulan sebagai indikator kualitas parsing Hermes. Tanpa target.

## 13. Risiko, asumsi, dan pertanyaan terbuka

| Tipe | Butir | Dampak | Tindak lanjut |
|---|---|---|---|
| Risiko | Perubahan UI baru sampai ke CSV STB hanya saat pull terjadwal, jawaban Hermes di antara jadwal bisa basi | Tinggi | FR-012 (pull sesuai permintaan) |
| Risiko | Dua orang mengedit baris yang sama; last-write-wins menimpa tanpa peringatan | Sedang | FR-017 (terakhir diubah oleh/pada) |
| Risiko | Hermes salah mengenali baris saat diminta edit/hapus lewat chat | Tinggi | Bertanya dulu bila ambigu (alur C) |
| Risiko | Kebocoran ADMIN_TOKEN memberi akses tulis penuh | Tinggi | Token acak ≥32 karakter, ganti via terminal STB; jangan di-commit ke GitHub |
| Risiko | Shared hosting: tanpa proses persisten dan sumber daya terbatas | Sedang | Polling terjadwal; foto struk ditunda |
| Risiko | Saldo per kantong tidak akurat bila `metode` kosong pada transaksi | Sedang | Penanda "kantong belum dipilih"; saldo awal dengan tanggal |
| Risiko | PHP di SSH berbeda dari PHP hPanel | Rendah | Atur `PATH` ke biner PHP 8.5 |
| Risiko | Agen pengembang melanggar aturan nilai (mis. menambah nilai `jenis`) | Sedang | Bagian 19 + uji otomatis |
| Asumsi | Volume data kecil sehingga tanpa paginasi kompleks | Rendah | Konfirmasi perkiraan volume |
| Asumsi | Dua akun UI (David, Nurul) | Sedang | Belum dikonfirmasi pengguna |
| Asumsi | Kategori tetap teks bebas (sesuai Pola Sinkron), UI menawarkan saran dari kategori yang ada | Rendah | Belum dikonfirmasi pengguna |

**Pertanyaan terbuka**
1. Persetujuan Hermes atas butir di Bagian 18.
2. Konfirmasi dua akun UI dan kategori tetap bebas.
3. Perkiraan volume transaksi per bulan (menentukan kebutuhan paginasi dan performa).

## 14. Rollout dan operasional

**Tahap 1 (Must):** semua fitur FR-001 sampai FR-010. Setelah selesai, pengguna men-deploy sendiri ke Hostinger lewat SSH, menjalankan migrasi 19 baris, lalu memakai paralel dengan CSV beberapa hari (durasi: TBD).

**Tahap 2 (Should):** FR-011 sampai FR-020, dirilis bertahap. Urutan **[Usulan]**: FR-020 saldo kantong, FR-013 dashboard, FR-016 kategori, FR-017 riwayat, FR-014 anggaran, FR-015 pengingat, FR-018 notifikasi Hermes, FR-012 pull sesuai permintaan, FR-011 ekspor, FR-019 PWA.

**Disarankan sejak Tahap 1 [Usulan]:** kolom `kantong_tujuan` ada di migrasi pertama dan API sudah menerimanya, serta perubahan transaksi sudah dicatat ke `transaksi_riwayat`, agar data Mutasi dan riwayat sejak hari pertama tidak perlu dilengkapi ulang.

**Rollback:** CSV lokal tetap ada sebagai cadangan sehingga Hermes dapat kembali membaca CSV bila API bermasalah. Pemilik insiden: TBD.

## 15. QA dan kriteria rilis

Skenario kritis:
- Kirim ulang pesan Telegram yang sama: tidak ada baris ganda.
- Hapus dari UI lalu pull STB: baris ditandai terhapus di CSV, tidak dibuang.
- Edit dari UI dan chat pada baris yang sama: hasil sesuai last-write-wins.
- Mutasi: tidak masuk total pengeluaran di UI maupun laporan Hermes; masuk perhitungan saldo kantong.
- Input jumlah "35rb" / "35.000": tersimpan 35000 atau ditolak, tidak pernah tersimpan sebagai string.
- Tanggal tanpa tahun dan tanpa tanggal terisi benar (WIB).
- Login: rate-limit bekerja; akses API tanpa token ditolak (401).
- HTTPS aktif; tidak ada konten campuran.
- Tampilan di HP: tidak ada scroll horizontal, target sentuh memadai.

Cacat pemblokir rilis: kehilangan data, duplikasi, total pengeluaran salah (termasuk Mutasi), kebocoran token. Persetujuan rilis: pengguna, setelah paralel run.

## 16. Keputusan dan riwayat perubahan

| Tanggal | Keputusan/perubahan | Sumber |
|---|---|---|
| 2026-11-02 | Hostinger primary, STB admin sinkron, polling terjadwal tanpa websocket | Pola Sinkron |
| 2026-11-02 | Draft PRD v0.1 dibuat | Percakapan |
| 2026-11-02 | Ditambah fitur lanjutan (Bagian 7A) | Permintaan pengguna |
| 2026-11-02 | Saldo per kantong diperlukan; foto struk ditunda; Laravel + PHP terbaru; Bagian 7B dan 7C | Permintaan pengguna |
| 2026-11-02 | PHP 8.5 target, kolom `kantong_tujuan` disetujui pengguna, hosting punya SSH | Jawaban pengguna |
| 2026-11-02 | Selisih saldo pilihan C; kantong awal Tunai, BSI, Shopee; mobile-first | Jawaban pengguna |
| 2026-11-02 | v0.2: semua Should masuk cakupan, rilis dua tahap, pembangun opencode + GitHub, Bagian 18 dan 19, dijadikan prd.md | Jawaban pengguna |
| 2026-11-02 | v0.3: 6 butir fit-Hermes ditempel (pencatat dipatok, normalisasi kategori, format since/server_time, header CSV 8 kolom, kontrak 422, saldo awal wajib); Bagian 18 disetujui Hermes | Hermes (STB) |

## 17. Glosarium

- **STB:** perangkat tempat agen Hermes berjalan.
- **Hermes:** agen pencatat keuangan yang menerima laporan via Telegram.
- **Mutasi:** perpindahan antar kantong (mis. tarik tunai dari bank ke kas), bukan belanja.
- **Kantong:** wadah uang (Tunai, BSI, Shopee), dipetakan dari kolom `metode`.
- **Soft delete:** penghapusan dengan mengisi `deleted_at`, tanpa menghapus baris.
- **Idempoten:** pengiriman ulang data yang sama tidak membuat baris baru (via `client_key`).
- **Pull / Push:** STB menarik data dari / mendorong data ke Hostinger.

Referensi: dokumen "Pola Sinkron: Hermes STB + Hostinger" (diunggah pengguna, 2026-11-02).

## 18. Checklist persetujuan untuk Hermes (STB)

Mohon Hermes menyetujui, menolak, atau mengusulkan perubahan untuk setiap butir. Larangan di Bagian 7 Pola Sinkron tetap dipatuhi.

| No | Butir yang perlu persetujuan | Status | Catatan Hermes |
|---|---|---|---|
| 1 | Kolom baru `kantong_tujuan` VARCHAR(30) NULL pada `transaksi`, wajib terisi bila `kategori=Mutasi`. Hermes membaca "dari X ke Y" dan mengirim `metode`=asal, `kantong_tujuan`=tujuan. Dikerjakan sejak Tahap 1 | Setuju | NULL-able, klien lama tanpa kolom ini tetap berfungsi |
| 2 | Bentuk respons `GET /api/transaksi`: usulan `{data:[...], next_offset, server_time}` (Pola Sinkron belum menetapkan) | Setuju | `since` tetap mengembalikan soft-deleted; `server_time` WIB, format dipatok di FR-007 |
| 3 | Respons POST/PUT/DELETE mengembalikan baris lengkap; POST dengan `client_key` yang sudah ada mengembalikan baris lama dengan status 200 | Setuju | Tanpa catatan |
| 4 | Endpoint tambahan Tahap 2: `GET /api/kantong`, `GET /api/anggaran`, `GET /api/pengingat`, `POST /api/pengingat/{id}/konfirmasi` | Setuju prinsip | Detail field dicek saat API jadi |
| 5 | Pull sesuai permintaan sebelum menjawab pertanyaan total atau saldo di chat (FR-012) | Setuju | Hanya untuk pertanyaan angka eksplisit; bila pull gagal jawab dari cache + penanda |
| 6 | Antrean lokal di STB dan kirim ulang bila API tidak terjangkau (aman karena idempoten) | Setuju | Antrean terbatas, urut, tetap pakai `client_key` |
| 7 | Hermes boleh mengedit dan menghapus (soft) lewat chat; bila kandidat baris lebih dari satu, bertanya dulu | Setuju | Tanpa catatan |
| 8 | Prefix `client_key` baru `ULANG-<id jadwal>-<YYYYMMDD>` untuk konfirmasi transaksi berulang | Setuju | Tanpa catatan |
| 9 | Pengingat tagihan dan peringatan anggaran dijalankan oleh cron Hermes dan dikirim lewat Telegram | Setuju | Jadwal cron baru digeser dari jam briefing |
| 10 | Laporan berkala memuat saldo per kantong dari `GET /api/kantong`; total belanja tetap mengecualikan Mutasi | Setuju | Saldo dihitung server, bukan dari CSV |
| 11 | Penanda "data mungkin belum terbaru" pada laporan bila pull gagal | Setuju | Tanpa catatan |
| 12 | `ADMIN_TOKEN` tetap terpisah dari token UI, minimal 32 karakter acak, diganti lewat terminal STB bila bocor | Setuju | Tanpa catatan |

## 19. Panduan implementasi untuk agen pengembang (opencode) [Usulan]

Panduan ini bisa disalin ke berkas instruksi agen (mis. AGENTS.md).

- **Repo dan alur:** satu repo monolit Laravel 13, PHP 8.5 (minimum 8.3), cabang `main` stabil, fitur dikerjakan di cabang terpisah lalu digabung lewat pull request, commit kecil.
- **Urutan Tahap 1:** (1) kerangka Laravel, zona waktu Asia/Jakarta, login UI; (2) migrasi `transaksi` sesuai Bagian 9 plus `kantong_tujuan` dan `transaksi_riwayat`; (3) API untuk STB beserta uji kontrak; (4) UI daftar, tambah, edit, hapus (mobile-first); (5) ringkasan periode; (6) importer 19 baris CSV; (7) uji skenario kritis Bagian 15.
- **Aturan yang tidak boleh dilanggar:** `jenis` hanya masuk/keluar; Mutasi = `jenis=keluar` + `kategori=Mutasi`; total belanja mengecualikan Mutasi; `jumlah` INT positif digit polos; hapus selalu soft delete; `updated_at` diset server; `client_key` UNIQUE dan idempoten. Jangan mengganti nama field, jangan menambah nilai `jenis`, jangan menyimpan jumlah sebagai string berformat, jangan membuat hapus permanen dari UI, jangan membuat UI offline-first.
- **Keamanan:** jangan pernah meng-commit `.env`, token, atau data asli; sediakan `.env.example` dengan nilai contoh; `ADMIN_TOKEN` hanya dari environment.
- **Definisi selesai Tahap 1:** semua kriteria FR-001 sampai FR-010 lulus, uji otomatis hijau, hasil impor 19 baris cocok dengan CSV, dan Hermes berhasil membaca `GET /api/transaksi`.
- **Deploy (dilakukan pengguna lewat SSH):** pastikan PHP di SSH adalah 8.5; `git pull`; `composer install --no-dev --optimize-autoloader`; `php artisan migrate --force`; `php artisan config:cache`, `route:cache`, `view:cache`. Cadangkan database sebelum migrasi. Aset front-end dibangun di komputer atau CI, tanpa Node di server; hasil build ikut di-commit ke cabang rilis atau diunggah manual (pilih satu saat setup).
