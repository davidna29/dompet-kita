# API Dompet Kita untuk Hermes (STB)

Semua endpoint di bawah `/api`. Wajib HTTPS di produksi.

## Autentikasi

```http
Authorization: Bearer <ADMIN_TOKEN>
```

`ADMIN_TOKEN` berasal dari environment Hostinger, minimal 32 karakter acak, terpisah dari sesi UI. Token tidak pernah dikirim ke browser UI.

Respons gagal:

```json
{ "error": "pesan singkat Bahasa Indonesia" }
```

- `401` token tidak valid.
- `404` data tidak ditemukan.
- `422` validasi gagal; `error` berisi pesan pertama.

Semua waktu memakai Asia/Jakarta, format `YYYY-MM-DDTHH:MM:SS` tanpa offset.

## Health

```http
GET /api/health
```

```json
{
  "status": "ok",
  "app": "Dompet Kita",
  "timezone": "Asia/Jakarta",
  "server_time": "2026-11-02T09:15:00"
}
```

## Transaksi

### Daftar

```http
GET /api/transaksi?since=2026-11-01T00:00:00&limit=100&offset=0
```

- `since` memfilter `updated_at >= since`, termasuk baris yang sudah soft-deleted.
- `limit` 1–500, default 100.
- `offset` default 0.
- Urut `updated_at`, lalu `id` naik agar aman untuk sinkronisasi.

```json
{
  "data": [
    {
      "id": 1,
      "tanggal": "2026-11-02",
      "jenis": "keluar",
      "kategori": "Makanan",
      "jumlah": 35000,
      "keterangan": "Belanja pasar",
      "pencatat": "David NA",
      "metode": "Tunai",
      "sumber": "telegram",
      "client_key": "TELEGRAM-123-456",
      "kantong_tujuan": null,
      "created_at": "2026-11-02T07:30:00",
      "updated_at": "2026-11-02T07:30:00",
      "deleted_at": null
    }
  ],
  "next_offset": null,
  "server_time": "2026-11-02T09:15:00"
}
```

Bila `next_offset` bukan null, panggil lagi dengan `offset` tersebut dan `since` yang sama sampai null.

### Buat (idempoten)

```http
POST /api/transaksi
```

```json
{
  "tanggal": "2026-11-02",
  "jenis": "keluar",
  "kategori": "Makanan",
  "jumlah": 35000,
  "keterangan": "Belanja pasar",
  "pencatat": "David NA",
  "metode": "Tunai",
  "sumber": "telegram",
  "client_key": "TELEGRAM-123-456",
  "kantong_tujuan": null
}
```

- Baru → `201` + `{data, server_time}`.
- `client_key` sudah ada → `200` + baris lama; tidak ada insert baru.
- Mutasi wajib `metode` (asal), `kantong_tujuan` (tujuan), keduanya berbeda.
- `pencatat` wajib untuk menjaga atribusi Telegram. `metode` tetap teks bebas/nullable pada API selama Hermes belum mengambil daftar kantong aktif; nama tak dikenal ditandai di UI.
- `jumlah` bilangan bulat positif.

### Ubah

```http
PUT /api/transaksi/{id}
```

Kirim field yang berubah. Respons `200` + baris lengkap. `updated_at` ditetapkan server hanya saat ada perubahan nilai. Baris soft-deleted tidak bisa diubah.

### Hapus (soft)

```http
DELETE /api/transaksi/{id}
```

Respons `200` + baris dengan `deleted_at` terisi. Baris tetap muncul di `GET /api/transaksi?since=` agar STB dapat menandai CSV.
DELETE berulang pada baris yang sama juga mengembalikan `200` + baris terhapus yang sama.

## Kantong

```http
GET /api/kantong
```

```json
{
  "data": [
    {
      "id": 1,
      "nama": "BSI",
      "saldo_awal": 4750000,
      "tanggal_saldo_awal": "2026-06-01",
      "masuk": 51625000,
      "keluar": 0,
      "terima_mutasi": 0,
      "saldo": 51625000,
      "urutan": 2
    }
  ],
  "server_time": "2026-11-02T09:15:00"
}
```

Saldo dihitung server. Jangan menghitung ulang dari CSV untuk laporan.

Rename kantong dari UI memperbarui `metode` dan `kantong_tujuan` pada semua transaksi (termasuk soft-deleted) serta jadwal berulang. Perubahan transaksi mencatat riwayat dan memperbarui `updated_at`, sehingga tersedia untuk STB melalui `GET /api/transaksi?since=`. Kantong arsip dikeluarkan dari daftar saldo aktif; referensi historisnya tetap dikenali.

## Anggaran

### Daftar / snapshot periode

```http
GET /api/anggaran?periode=2026-11
```

`periode` opsional, format `YYYY-MM`, default bulan berjalan. Bila periode yang diminta belum punya anggaran, server meminjam anggaran terdekat sebelumnya dan mengisi `dipinjam: true` serta `periode_sebelumnya`.

```json
{
  "data": {
    "periode": "2026-11",
    "dipinjam": false,
    "periode_sebelumnya": null,
    "total_anggaran": 7500000,
    "total_terpakai": 4650000,
    "total": { "id": 10, "kategori": "Total bulanan", "anggaran": 7500000, "terpakai": 4650000, "sisa": 2850000, "persen": 62, "ambang": 80, "status": "aman" },
    "rows": [
      { "id": 11, "kategori": "Makanan", "anggaran": 2200000, "terpakai": 1873000, "sisa": 327000, "persen": 85, "ambang": 75, "status": "hampir" }
    ]
  },
  "server_time": "2026-11-02T09:15:00"
}
```

`status`: `aman`, `hampir`, atau `lewat`. Mutasi dan pemasukan tidak dihitung. Baris hasil pinjaman bulan sebelumnya memiliki `id: null`; baris itu tidak dapat dihapus dari periode pinjaman.

### Simpan / perbarui (upsert idempoten)

```http
POST /api/anggaran
```

```json
{
  "periode": "2026-11",
  "kategori": "Makanan",
  "jumlah": 2200000,
  "ambang": 75
}
```

- `kategori: null` atau `"__total__"` menyimpan batas total bulanan. Field `kategori` wajib hadir, boleh `null`.
- Kategori dinormalisasi trim, spasi dirapatkan, dan title case; `Mutasi` ditolak karena bukan belanja.
- `jumlah` integer positif; `ambang` 1–100, default 80.
- Baris baru → `201`; baris yang sudah ada atau dipulihkan dari soft delete → `200`.
- `dibuat` menandakan insert baru. `row` berisi baris yang disimpan, `data` berisi snapshot periode terbaru.

### Hapus (soft delete, idempoten)

```http
DELETE /api/anggaran/{id}
```

Mengembalikan `200` + snapshot + `dihapus: true`. DELETE berulang pada id yang sama tetap `200`. Baris yang sudah soft-deleted tidak tampil sebagai milik periode itu; gunakan `POST` untuk membuat baris periode pengganti.

### Salin dari bulan sebelumnya

```http
POST /api/anggaran/salin
```

```json
{ "periode": "2026-11" }
```

Menyalin seluruh baris anggaran dari periode terdekat sebelumnya yang tersedia. `jumlah_disalin: 0` bila periode tujuan sudah punya baris (termasuk soft-deleted) atau tidak ada periode sebelumnya. Tidak menimpa data yang ada.

## Kategori

```http
GET /api/kategori
```

Read-only. Menggabungkan kategori dari transaksi tidak terhapus dan `kategori_meta` aktif. Tanpa meta, ikon memakai fallback `Icon::untukKategori()` dan warna `#496976`.

```json
{
  "data": [
    {
      "kategori": "Makanan",
      "ikon": "utensils",
      "warna": "#A83B24",
      "urutan": 1,
      "ada_meta": true,
      "mutasi": false,
      "jumlah_transaksi": 12,
      "total": 1873000,
      "masuk": { "jumlah_transaksi": 0, "total": 0 },
      "keluar": { "jumlah_transaksi": 12, "total": 1873000 }
    },
    {
      "kategori": "Mutasi",
      "ikon": "repeat",
      "warna": "#87570F",
      "urutan": 10,
      "ada_meta": true,
      "mutasi": true,
      "jumlah_transaksi": 3,
      "total": 1200000,
      "masuk": { "jumlah_transaksi": 0, "total": 0 },
      "keluar": { "jumlah_transaksi": 3, "total": 1200000 }
    }
  ],
  "server_time": "2026-11-02T09:15:00"
}
```

Urutan baris alfabetis. Ikon, warna, dan urutan tetap dikelola dari UI; tidak ada endpoint tulis kategori.

## Pengingat / transaksi berulang

### Daftar

```http
GET /api/pengingat
GET /api/pengingat?semua=1
```

Default hanya jadwal aktif. `semua=1` ikut menampilkan jadwal nonaktif; jadwal soft-deleted tidak pernah dikembalikan. Setiap baris memuat field jadwal, `status`, `client_key`, `client_key_konfirmasi`, `terakhir_dikonfirmasi`, dan timestamp.

### Buat

```http
POST /api/pengingat
```

```json
{
  "nama": "Listrik PLN",
  "kategori": "Tagihan",
  "jumlah": 480000,
  "aturan": "bulanan",
  "jatuh_tempo": "2026-11-05",
  "metode": "BSI",
  "pencatat": "David NA",
  "aktif": true,
  "client_key": "TELEGRAM-123-456"
}
```

- `kategori` dinormalisasi title case. `Mutasi` membutuhkan `metode` dan `kantong_tujuan` yang berbeda; selain Mutasi `kantong_tujuan` disimpan `null`.
- `client_key` opsional dan idempoten. Kirim key yang sama untuk retry aman; POST berulang mengembalikan jadwal lama dengan `200` dan `dibuat: false`. Baru → `201`.
- `client_key` yang sudah terpakai oleh jadwal soft-deleted mengembalikan baris itu apa adanya dengan `deleted_at` terisi; server tidak menghidupkannya otomatis.
- `aktif` default `true` saat membuat.
- `pencatat` wajib; `jumlah` integer positif.

### Ubah

```http
PUT /api/pengingat/{id}
```

Kirim field yang berubah. `client_key` tidak dapat diubah dan akan ditolak `422` bila dikirim. PUT pada jadwal soft-deleted → `404`; tidak ada restore lewat PUT.

### Hapus (soft)

```http
DELETE /api/pengingat/{id}
```

Respons `200` + baris dengan `deleted_at` terisi dan `dihapus: true`. DELETE berulang pada id yang sama tetap `200`.

### Konfirmasi

```http
POST /api/pengingat/{id}/konfirmasi
```

```json
{
  "jumlah": 480000,
  "jatuh_tempo": "2026-11-05",
  "tanggal": "2026-11-05",
  "keterangan": "Listrik PLN"
}
```

- `jatuh_tempo` sebaiknya dikirim persis seperti yang dibaca dari daftar; ini kunci idempotensi.
- Key yang dibuat server: `ULANG-<id jadwal>-<YYYYMMDD>`.
- Konfirmasi pertama → `201` + `{data, dibuat: true}`.
- Konfirmasi ulang untuk jatuh tempo yang sama → `200` + baris lama, `dibuat: false`.
- Transaksi baru memakai `sumber=telegram` saat dipanggil dari STB.

## Pemetaan CSV STB

Header tetap:

```csv
tanggal,jenis,kategori,jumlah,keterangan,pencatat,metode,kantong_tujuan
```

- `jumlah` digit polos.
- `kantong_tujuan` kosong untuk non-Mutasi.
- Baris `deleted_at` tidak dibuang; tandai di CSV dan pertahankan sampai backup bulanan.
- Laporan belanja mengecualikan kategori `Mutasi`; saldo kantong memasukkannya.
