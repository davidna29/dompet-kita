# Dompet Kita

Aplikasi keuangan keluarga mobile-first: catat lewat agen Hermes di Telegram, lihat dan koreksi lewat papan web. Satu sumber data di Hostinger, dengan CSV di STB sebagai cache dan cadangan.

Dokumen produk dan aturan nilai: [`docs/prd-ok.md`](docs/prd-ok.md). Kontrak API: [`docs/api.md`](docs/api.md).

## Yang sudah dibangun

Tahap 1 dan Tahap 2 (FR-001 sampai FR-020):

- login dua akun (David, Nurul) dengan rate limit 5 percobaan/menit/IP;
- daftar transaksi dengan filter tanggal, jenis, kategori, pencatat, dan kantong;
- tambah, ubah, dan hapus lunak; riwayat perubahan; nomor client_key untuk idempotensi;
- ringkasan periode, grafik donat/batang/kumulatif, dan perbandingan bulan lalu;
- saldo per kantong Tunai/BSI/Shopee, rumus server, rekonsiliasi tanpa penyesuaian otomatis;
- anggaran per kategori dan total, ambang 80%/100%, salinan bulan lalu;
- jadwal berulang dan konfirmasi tagihan satu ketukan (idempoten per jatuh tempo);
- ikon, warna, dan urutan kategori;
- ekspor CSV dengan header STB, impor CSV migrasi 19 baris;
- API Bearer token untuk Hermes, termasuk soft-deleted di `since`;
- PWA + mode gelap, online-only; service worker hanya menyimpan aset statis.

Could (target tabungan, hutang/piutang, kalender, pencarian, tag, PDF/Excel, foto struk) belum dibangun sesuai PRD.

## Kebutuhan

- PHP 8.3+ (target produksi 8.5, dikembangkan di 8.4)
- Composer 2
- Node.js 20+ dan npm
- MySQL 8 (produksi) atau SQLite (pengembangan lokal)
- HTTPS di produksi

## Menjalankan lokal

```bash
composer install
cp .env.example .env        # Windows: copy .env.example .env
php artisan key:generate

# Untuk SQLite lokal, ubah .env:
# DB_CONNECTION=sqlite
# DB_DATABASE=C:/absolute/path/database/database.sqlite
# DEMO_MODE=true

touch database/database.sqlite  # Windows: New-Item database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Akun demo

Hanya dibuat bila `DEMO_MODE=true` dan `php artisan db:seed` dijalankan. Data yang muncul adalah **data sintetis**, bukan riwayat keluarga asli.

| Akun | Email | Kata sandi |
|---|---|---|
| David NA | `david@dompetkita.test` | `password` |
| Nurul Azizah | `nurul@dompetkita.test` | `password` |

Jangan pernah mengaktifkan `DEMO_MODE` atau memakai akun demo di produksi.

## Perintah penting

```bash
php artisan test                         # 42 tes: API, zona WIB, aturan Mutasi, saldo, idempotensi, impor
php artisan migrate --force              # produksi
php artisan db:seed --class=DemoSeeder   # hanya lokal, data sintetis
php artisan transaksi:impor-csv path/to/transaksi.csv --dry-run
php artisan transaksi:impor-csv path/to/transaksi.csv
npm run build                            # hasil di public/build, unggah ke server
```

### Format CSV impor

Header wajib:

```csv
tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan
2026-11-02,keluar,Makanan,35000,Belanja pasar,David NA,Tunai,
2026-11-02,keluar,Mutasi,500000,Tarik tunai,David NA,BSI,Tunai
```

- `jumlah` digit polos tanpa titik.
- `kantong_tujuan` boleh kosong untuk non-Mutasi.
- `client_key` dibuat otomatis `MIGRASI-<nomor>`.
- Rerun aman: baris dengan client_key yang sama dilewati.
- Bila satu baris gagal, seluruh impor dibatalkan; perbaiki file lalu ulangi.

## API untuk Hermes (STB)

Token admin terpisah dari sesi UI. Isi `ADMIN_TOKEN` di environment, minimal 32 karakter acak:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Contoh:

```bash
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  "https://domain-anda/api/transaksi?since=2026-11-01T00:00:00&limit=100"

curl -X POST "https://domain-anda/api/transaksi" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"tanggal":"2026-11-02","jenis":"keluar","kategori":"Makanan","jumlah":35000,"keterangan":"Belanja pasar","pencatat":"David NA","metode":"Tunai","sumber":"telegram","client_key":"TELEGRAM-123-456"}'
```

Jangan pernah menaruh token di repo, PRD, chat, atau tangkapan layar. Ganti token lewat terminal STB bila bocor.

## Deploy Hostinger (dilakukan pengguna)

1. Aktifkan HTTPS dan pastikan domain mengarah ke `public/`.
2. Pilih PHP 8.5 di hPanel. Di SSH, arahkan PATH ke biner PHP yang sama (mis. `/opt/alt/php85/usr/bin`) bila versi SSH tertinggal.
3. Cadangkan database sebelum migrasi.
4. `git pull` (atau unggah rilis), lalu:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Buat `.env` dari `.env.example`; isi `APP_KEY`, kredensial MySQL, `APP_URL` HTTPS, dan `ADMIN_TOKEN`. Jangan pakai `DEMO_MODE=true`.
6. Unggah hasil `npm run build` (folder `public/build`) karena server tidak menjalankan Node.
7. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis.
8. Setelah deploy, buat dua akun produksi. Jangan menjalankan seeder demo.
9. Uji `GET /api/health` dari STB, lalu lakukan paralel run dengan CSV beberapa hari sebelum mengganti alur lama.
10. Sebelum impor CSV, buka **Kantong → Atur saldo awal** dan isi saldo nyata Tunai, BSI, Shopee beserta tanggal hitungnya. Tanpa ini saldo kantong tidak akurat.

Tidak ada worker permanen, Redis, websocket, atau queue daemon. Gunakan `QUEUE_CONNECTION=sync` dan driver cache/sesi database.

## Aturan yang tidak boleh dilanggar

- `jenis` hanya `masuk` dan `keluar`.
- Mutasi = `jenis=keluar` + `kategori=Mutasi` + `metode` asal + `kantong_tujuan` tujuan yang berbeda.
- Total belanja selalu mengecualikan Mutasi; saldo kantong memasukkan Mutasi.
- `jumlah` selalu bilangan bulat positif digit polos; tanda ditentukan `jenis`.
- Penghapusan selalu soft delete; baris tetap muncul di API `since`.
- `client_key` unik dan idempoten; `updated_at` ditetapkan server.
- UI tidak menyimpan data transaksi di perangkat dan tidak offline-first.

## Struktur singkat

- `app/Http/Controllers` — halaman dan API
- `app/Services` — aturan transaksi, saldo, ringkasan, anggaran, pengingat
- `app/Livewire` — komponen form transaksi (Livewire 4 single-file di `resources/views/components/⚡transaksi-form.blade.php`)
- `database/migrations` — skema `transaksi`, `kantong`, `anggaran`, `transaksi_berulang`, `transaksi_riwayat`, `kategori_meta`
- `resources/css/app.css` — token Papan Dapur, komponen, mode gelap
- `public/manifest.webmanifest`, `public/sw.js`, `public/offline.html` — PWA online-only
- `tests` — kontrak API dan aturan nilai

## Catatan desain

Arah visual: **Papan Dapur** — krem kapur, zaitun, tomat, oker, tipografi Bricolage Grotesque + Karla + Azeret Mono, slip transaksi dan magnet yang fungsional. Keputusan ini tercatat di `.impeccable/surfaces/beranda.md`. `DESIGN.md` dan sidecar `.impeccable/design.json` memuat sistem visual lengkap.

## Verifikasi rilis

- `php artisan test` — 42 tes, 142 assertion (API, WIB/`since`, idempotensi, Mutasi, saldo, anggaran, impor, login throttle).
- `impeccable detect` pada `resources/views`, `resources/css`, `resources/js` — 0 temuan.
- Tangkapan 390 dan 1440, termasuk mode gelap, ada di `.impeccable/review/`.
- PWA PNG di `public/icons/` membawa provenance yang disematkan. `public/favicon.ico` dihasilkan dari `favicon-32.png` oleh `scripts/make-favicon.php`.
- Uji manual: `35rb` di form menjadi `35.000`; API `since` memakai WIB; pagination Indonesia; target sentuh utama 44 px; tidak ada scroll horizontal di 360 px.
