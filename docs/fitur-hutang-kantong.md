# Prompt untuk Opencode — Fitur Kantong Tipe + Utang-Piutang

Copy-paste prompt ini ke Opencode (desktop).

---

Kamu adalah developer Dompet Kita (Laravel 13, PHP 8.5, MySQL, Blade+Livewire+Tailwind). Repo: davidna29/dompet-kita, branch main. Jangan ubah yang tidak diminta.

## Tugas
Implementasi dua fitur Tahap 2b sesuai PRD v0.4+:

### 1. Kantong: tambah tipe
- Migration: `add_tipe_to_kantong_table` → tambah kolom `tipe ENUM('harian','tabungan','investasi') NOT NULL DEFAULT 'harian'` ke tabel `kantong`. Data lama isi `harian`. Index biasa.
- Model `Kantong`: fillable + cast `tipe`.
- Service `SaldoService`: method `semua()` tetap, tambah helper `totalByTipe()` yang group by tipe. `KantongService::gantiNama()` dan `arsipkan()` tetap.
- API `GET /api/kantong` tetap, tapi response tiap kantong tambah field `tipe`. Tidak ada POST kantong via API (tetap UI).
- UI `Kantong` (Livewire): di form buat/edit tambah select tipe (harian/tabungan/investasi). Di daftar tampil badge tipe + filter. Di dashboard ringkasan tampil 3 total terpisah.
- Seeder: update `DemoSeeder` 3 kantong jadi Tunai=harian, BSI=harian, Shopee=harian (default).

### 2. Utang-Piutang (FR-023)
- Migration `create_hutang_piutang_table`: `id BIGINT PK, pihak VARCHAR(50) NOT NULL, jenis ENUM('hutang','piutang') NOT NULL, nominal INT NOT NULL, sisa INT NOT NULL, tanggal DATE NOT NULL, jatuh_tempo DATE NULL, status ENUM('belum','lunas') DEFAULT 'belum', keterangan TEXT NULL, timestamps, softDeletes`. Index `jenis, status, jatuh_tempo`.
- Model `HutangPiutang`: SoftDeletes, fillable, cast.
- Service `HutangService`:
  - `buat(data, user)`: validasi, `sisa = nominal`, `status='belum'`, transaksi TIDAK dibuat otomatis.
  - `bayar(hutang, jumlah, tanggal, metode, keterangan, user)`: validasi jumlah <= sisa, buat `Transaksi` via `TransaksiService::buat()` dengan `jenis=keluar` jika hutang (bayar hutang) atau `masuk` jika piutang (terima), `kategori=Hutang`/`Piutang`, kurangi `sisa`, jika sisa 0 set `status='lunas'`, dalam DB::transaction + lockForUpdate. Idempoten via `client_key = HUTANG-<id>-<YYYYMMDD>-<jumlah>`.
  - `hapus()` soft delete.
- Requests: `StoreHutangRequest`, `BayarHutangRequest` dengan pesan Bahasa Indonesia.
- Controllers `Api\HutangApiController`: `index` (filter jenis/status), `store`, `bayar`, `destroy` (soft). Semua di middleware `admin.token` versi `/api`. Response JSON `{data, server_time}` konsisten dengan `TransaksiResource`.
- Routes `routes/api.php`: `GET /hutang`, `POST /hutang`, `POST /hutang/{hutang}/bayar`, `DELETE /hutang/{hutang}`.
- Web UI: menu baru `Hutang` (Livewire) list + form tambah + detail bayar/cicil + badge sisa. Jangan ubah `jenis` transaksi.
- Tests: `ApiHutangTest` (buat, bayar sebagian, bayar lunas, idempoten bayar, validasi).

## Aturan yang tidak boleh dilanggar (dari AGENTS.md)
- `transaksi.jenis` tetap hanya `masuk`/`keluar`. Mutasi tetap `jenis=keluar`+`kategori=Mutasi`.
- `jumlah` INT digit polos, `updated_at` server Asia/Jakarta, soft delete, `client_key` unique idempoten.
- Jangan copy logic transaksi ke controller, pakai `TransaksiService`.
- Jangan expose stock/balance writable, jangan hard-delete transaksi.
- `ADMIN_TOKEN` tetap dari env, jangan commit.

## Verifikasi
- `php artisan migrate --pretend` cek SQL
- `herd php artisan test` (atau `php artisan test`) harus hijau, termasuk test baru
- `npm run build`
- `curl` manual: `POST /api/hutang` + `POST /api/hutang/{id}/bayar` dengan `Authorization: Bearer $ADMIN_TOKEN`

Kerjakan di branch baru `feat/kantong-tipe-hutang`, commit kecil, PR ke main.

