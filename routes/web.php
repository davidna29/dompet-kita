<?php

use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\BerulangController;
use App\Http\Controllers\EksporController;
use App\Http\Controllers\HutangController;
use App\Http\Controllers\KantongController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RingkasanController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('beranda'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/beranda', BerandaController::class)->name('beranda');

    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/baru', [TransaksiController::class, 'create'])->name('transaksi.create');
    Route::get('/transaksi/{transaksi}/edit', [TransaksiController::class, 'edit'])->name('transaksi.edit');
    Route::delete('/transaksi/{transaksi}', [TransaksiController::class, 'destroy'])->name('transaksi.destroy');
    Route::get('/transaksi/ekspor/csv', [EksporController::class, 'transaksi'])->name('transaksi.ekspor');

    Route::get('/ringkasan', [RingkasanController::class, 'index'])->name('ringkasan');
    Route::get('/kantong', [KantongController::class, 'index'])->name('kantong');
    Route::get('/kantong/baru', [KantongController::class, 'create'])->name('kantong.create');
    Route::post('/kantong', [KantongController::class, 'store'])->name('kantong.store');
    Route::get('/kantong/{kantong}/edit', [KantongController::class, 'edit'])->name('kantong.edit');
    Route::put('/kantong/{kantong}', [KantongController::class, 'update'])->name('kantong.update');
    Route::delete('/kantong/{kantong}', [KantongController::class, 'destroy'])->name('kantong.destroy');
    Route::post('/kantong/arsip/{id}/pulihkan', [KantongController::class, 'pulihkan'])->name('kantong.pulihkan');
    Route::post('/kantong/rekonsiliasi', [KantongController::class, 'rekonsiliasi'])->name('kantong.rekonsiliasi');
    Route::post('/kantong/saldo-awal', [KantongController::class, 'simpanSaldoAwal'])->name('kantong.saldo-awal');

    Route::get('/anggaran', [AnggaranController::class, 'index'])->name('anggaran.index');
    Route::post('/anggaran', [AnggaranController::class, 'store'])->name('anggaran.store');
    Route::delete('/anggaran/{anggaran}', [AnggaranController::class, 'destroy'])->name('anggaran.destroy');
    Route::post('/anggaran/salin', [AnggaranController::class, 'salin'])->name('anggaran.salin');

    Route::get('/berulang', [BerulangController::class, 'index'])->name('berulang.index');
    Route::get('/berulang/baru', [BerulangController::class, 'create'])->name('berulang.create');
    Route::post('/berulang', [BerulangController::class, 'store'])->name('berulang.store');
    Route::get('/berulang/{berulang}/edit', [BerulangController::class, 'edit'])->name('berulang.edit');
    Route::put('/berulang/{berulang}', [BerulangController::class, 'update'])->name('berulang.update');
    Route::delete('/berulang/{berulang}', [BerulangController::class, 'destroy'])->name('berulang.destroy');
    Route::post('/berulang/{berulang}/konfirmasi', [BerulangController::class, 'konfirmasi'])->name('berulang.konfirmasi');

    Route::get('/hutang', [HutangController::class, 'index'])->name('hutang.index');
    Route::get('/hutang/baru', [HutangController::class, 'create'])->name('hutang.create');
    Route::post('/hutang', [HutangController::class, 'store'])->name('hutang.store');
    Route::get('/hutang/{hutang}', [HutangController::class, 'show'])->name('hutang.show');
    Route::post('/hutang/{hutang}/bayar', [HutangController::class, 'bayar'])->name('hutang.bayar');
    Route::delete('/hutang/{hutang}', [HutangController::class, 'destroy'])->name('hutang.destroy');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::delete('/kategori/{kategoriMeta}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan');
    Route::post('/pengaturan/pengguna', [PenggunaController::class, 'store'])->name('pengaturan.pengguna.store');
});
