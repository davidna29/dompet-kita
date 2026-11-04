<?php

use App\Http\Controllers\Api\AnggaranApiController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\KantongApiController;
use App\Http\Controllers\Api\KategoriApiController;
use App\Http\Controllers\Api\PengingatApiController;
use App\Http\Controllers\Api\TransaksiApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.token')->group(function () {
    Route::get('/health', HealthController::class)->name('api.health');

    Route::get('/transaksi', [TransaksiApiController::class, 'index'])->name('api.transaksi.index');
    Route::post('/transaksi', [TransaksiApiController::class, 'store'])->name('api.transaksi.store');
    Route::put('/transaksi/{transaksi}', [TransaksiApiController::class, 'update'])->name('api.transaksi.update');
    Route::delete('/transaksi/{transaksi}', [TransaksiApiController::class, 'destroy'])->name('api.transaksi.destroy');

    Route::get('/kantong', [KantongApiController::class, 'index'])->name('api.kantong.index');
    Route::get('/anggaran', [AnggaranApiController::class, 'index'])->name('api.anggaran.index');
    Route::post('/anggaran', [AnggaranApiController::class, 'store'])->name('api.anggaran.store');
    Route::post('/anggaran/salin', [AnggaranApiController::class, 'salin'])->name('api.anggaran.salin');
    Route::delete('/anggaran/{anggaran}', [AnggaranApiController::class, 'destroy'])->name('api.anggaran.destroy');

    Route::get('/kategori', [KategoriApiController::class, 'index'])->name('api.kategori.index');

    Route::get('/pengingat', [PengingatApiController::class, 'index'])->name('api.pengingat.index');
    Route::post('/pengingat', [PengingatApiController::class, 'store'])->name('api.pengingat.store');
    Route::put('/pengingat/{pengingat}', [PengingatApiController::class, 'update'])->name('api.pengingat.update');
    Route::delete('/pengingat/{pengingat}', [PengingatApiController::class, 'destroy'])->name('api.pengingat.destroy');
    Route::post('/pengingat/{pengingat}/konfirmasi', [PengingatApiController::class, 'konfirmasi'])
        ->name('api.pengingat.konfirmasi');
});
