# AGENTS.md — Dompet Kita

Panduan ini mengikat agen yang mengubah aplikasi. Sumber kebenaran produk ada di `docs/prd-ok.md`; PRODUCT.md merangkum konteks; `.impeccable/surfaces/beranda.md` memuat arah visual.

## Perintah

```bash
php artisan test
php artisan migrate:fresh --seed   # hanya lokal
npm run build
php artisan transaksi:impor-csv <path> [--dry-run]
```

## Aturan data yang tidak boleh dilanggar

- `jenis` hanya `masuk` / `keluar`. Jangan menambah nilai baru.
- Mutasi = `jenis=keluar` + `kategori=Mutasi`; `metode` = asal, `kantong_tujuan` = tujuan, keduanya wajib dan berbeda.
- Semua total belanja/pengeluaran mengecualikan Mutasi. Saldo kantong memasukkan Mutasi.
- `jumlah` selalu bilangan bulat positif, disimpan digit polos (INT), bukan string berformat.
- Penghapusan selalu soft delete. Jangan membuat hapus permanen dari UI.
- `client_key` UNIQUE dan idempoten. POST dengan key sama mengembalikan baris lama.
- `updated_at` selalu ditetapkan server (Asia/Jakarta).
- Kategori tetap teks bebas (maks 50), normalisasi trim + title case. Meta kategori ada di `kategori_meta`.
- `pencatat` dari UI dipetakan dari akun login, bukan input bebas.
- Jangan menambah field dengan nama berbeda dari PRD/API. Nama field API = nama kolom.

## Keamanan

- Jangan commit `.env`, token, atau data asli.
- `ADMIN_TOKEN` hanya dari environment; token UI terpisah.
- Jangan tampilkan token di UI, log, test, atau chat.
- Jangan menyimpan data transaksi di localStorage/IndexedDB. Hanya preferensi tema.
- API dan UI produksi wajib HTTPS.

## Stack dan batasan hosting

- Laravel 13, PHP 8.3+ (target 8.5), MySQL utf8mb4 produksi.
- Blade + Livewire + Tailwind. Chart.js dibundel lewat Vite.
- Tanpa Redis, worker permanen, websocket, atau Node di server.
- Aset dibangun lokal/CI lalu diunggah; `public/build` tidak dijalankan di server.
- Zona waktu `Asia/Jakarta`; locale `id`.

## Desain

- Arah: Papan Dapur. Krem kapur, zaitun, tomat, oker, sky; Bricolage Grotesque display, Karla body/amount, Azeret Mono metadata.
- Mobile-first 360–390 px; target sentuh ≥44 px; tidak ada scroll horizontal.
- Slip transaksi, magnet/pin fungsional, bulan aktif. Jangan menambah tekstur scrapbook, tulisan tangan, atau ornamen dekoratif.
- Mode gelap dan terang harus tetap kontras.
- Setiap loading, kosong, galat, sukses, dan konfirmasi hapus harus nyata.
- Jangan mengubah salinan fakta/Hermes tanpa alasan.

## Selesai berarti

- Semua tes hijau.
- `FR-001`–`FR-020` dapat ditemukan di UI/API.
- Impor 19 baris cocok dengan CSV asli saat file tersedia.
- `GET /api/transaksi` terbaca oleh STB.
- Halaman diperiksa di 360 px dan desktop, tanpa error konsol.
