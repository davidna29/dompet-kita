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

```http
GET /api/anggaran?periode=2026-11
```

`periode` opsional, format `YYYY-MM`, default bulan berjalan.

```json
{
  "data": {
    "periode": "2026-11",
    "dipinjam": false,
    "periode_sebelumnya": null,
    "total_anggaran": 7500000,
    "total_terpakai": 4650000,
    "total": { "kategori": "Total bulanan", "anggaran": 7500000, "terpakai": 4650000, "sisa": 2850000, "persen": 62, "ambang": 80, "status": "aman" },
    "rows": [
      { "kategori": "Makanan", "anggaran": 2200000, "terpakai": 1873000, "sisa": 327000, "persen": 85, "ambang": 75, "status": "hampir" }
    ]
  },
  "server_time": "2026-11-02T09:15:00"
}
```

`status`: `aman`, `hampir`, atau `lewat`. Mutasi dan pemasukan tidak dihitung.

## Pengingat / transaksi berulang

### Daftar

```http
GET /api/pengingat
```

Mengembalikan jadwal aktif dengan `jatuh_tempo`, `status`, `client_key_konfirmasi`, dan `terakhir_dikonfirmasi`.

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
