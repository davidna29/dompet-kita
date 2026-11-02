# Rangkuman Perbaikan Dompet Kita (untuk opencode)

Tanggal: 2026-11-02
Repo: https://github.com/davidna29/dompet-kita (diunduh ke STB, dibaca tanpa dijalankan)
Acuan: PRD v0.3 (`/root/keuangan-keluarga/prd.md`), `AGENTS.md`, `docs/api.md`

Catatan status: semua temuan berasal dari membaca kode. Belum ada yang diuji dengan `php artisan test`, karena PHP tidak tersedia di STB. Verifikasi wajib dilakukan di desktop.

---

## A. Perbaikan API (audit pertama)

1. **POST `/api/transaksi`: `pencatat` wajib, padahal seharusnya opsional.**
   - Lokasi: `app/Http/Requests/Api/StoreTransaksiRequest.php` (`'pencatat' => ['required', ...]`)
   - Service `TransaksiService::normalisasi()` sudah punya nilai cadangan ('Tanpa nama'), jadi aturan request cukup dilonggarkan jadi `nullable`.

2. **PUT/DELETE ke baris yang sudah dihapus (soft) menghasilkan 404.**
   - Dari pembacaan kode: route model binding `Transaksi $transaksi` tidak memakai `withTrashed()`.
   - Perilaku yang diinginkan:
     - PUT ke baris terhapus → hidupkan lagi (restore), lalu terapkan perubahan, kembalikan 200.
     - DELETE ke baris yang sudah terhapus → 200 dengan baris apa adanya (service sudah melakukan ini, hanya route yang menghalangi).
   - Keputusan: perubahan perilaku ini belum disetujui pengguna (lihat bagian D).

3. **PUT tanpa perubahan tetap menggeser `updated_at`.**
   - Lokasi: `TransaksiService::perbarui()`, cabang `else { $transaksi->touch(); }`
   - Dampak: baris ikut muncul lagi di `GET /api/transaksi?since=` padahal isinya tidak berubah.
   - Perbaikan: hapus `touch()`, kembalikan baris apa adanya.

4. **Endpoint restore belum ada (usulan).**
   - `POST /api/transaksi/{id}/restore` — membuat restore eksplisit, tidak bergantung pada PUT.
   - Keputusan: belum disetujui pengguna (lihat bagian D).

## B. Perbaikan lanjutan (audit kedua)

5. **`metode` dan `kantong_tujuan` tidak divalidasi terhadap tabel `kantong`.**
   - Dampak: typo seperti "Shoppe" membuat transaksi tidak terhitung di saldo kantong mana pun. Mutasi dengan `kantong_tujuan` salah membuat saldo kantong tujuan yang benar tidak bertambah.
   - Perbaikan: tolak dengan 422 bila nama tidak cocok dengan kantong aktif (`deleted_at` kosong). Pesan singkat Bahasa Indonesia.

6. **Paging dengan `offset` bisa melewatkan baris.**
   - Lokasi: `TransaksiApiController::index()`, urut `updated_at` lalu `id`, memakai `offset`.
   - Dampak: baris yang diedit saat pull berlangsung pindah ke ujung urutan; baris di batas halaman bisa terlewat. Dengan volume saat ini risikonya kecil.
   - Perbaikan: keyset cursor (`updated_at`, `id`). Ini mengubah kontrak `next_offset` yang sudah tertulis di PRD dan `docs/api.md`.

7. **Nama kantong yang sudah soft-delete tetap memblokir nama itu.**
   - Lokasi: migrasi `create_kantong_table`, `->unique()` pada `nama`, sementara model `Kantong` memakai `SoftDeletes`.
   - Perbaikan: unique index hanya untuk baris aktif, atau tolak dengan pesan yang jelas.

8. **Checklist deploy produksi (`.env` Hostinger):**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://...`
   - `SESSION_SECURE_COOKIE=true`
   - `ADMIN_TOKEN` terisi (minimal 32 karakter acak, tidak di-commit)

## C. Koreksi PRD v0.3

9. **Normalisasi kategori.** PRD menulis "huruf pertama kapital, sisanya kecil". Kode dan AGENTS.md memakai title case per kata (`mb_convert_case(..., MB_CASE_TITLE)`), contohnya `makan siang` → `Makan Siang`. PRD akan disesuaikan dengan kode.

## D. Keputusan yang masih menunggu pengguna

- **#2 (PUT menghidupkan baris terhapus)** dan **#6 (keyset cursor)** mengubah perilaku yang sudah tertulis di PRD dan `docs/api.md`. Jika disetujui, PRD naik ke v0.4.
- **#4 (endpoint restore)** adalah tambahan API. Perlu persetujuan.

## E. Verifikasi di desktop (wajib sebelum deploy)

```bash
php artisan test
php artisan migrate:fresh --seed        # lokal saja
php artisan transaksi:impor-csv <path> --dry-run
```

Uji tambahan yang perlu ditambahkan:
- POST dengan `client_key` yang sama dua kali → 201 lalu 200, satu baris.
- Mutasi dengan kantong tidak dikenal → 422 (setelah #5).
- PUT/DELETE pada baris terhapus (setelah #2).
- PUT tanpa perubahan tidak mengubah `updated_at` (setelah #3).
- Paging pull tidak melewatkan baris saat ada edit di tengah (setelah #6).
- Total pengeluaran tidak menyertakan Mutasi; saldo kantong menyertakan Mutasi (sudah ada di `PerhitunganTest`, pastikan tetap hijau).
