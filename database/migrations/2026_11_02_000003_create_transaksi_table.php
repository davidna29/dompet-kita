<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->index();
            $table->string('jenis', 10)->index();
            $table->string('kategori', 50)->index();
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();
            $table->string('pencatat', 50)->index();
            $table->string('metode', 30)->nullable()->index();
            $table->string('sumber', 20);
            $table->string('client_key', 64)->nullable()->unique();
            $table->string('kantong_tujuan', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
