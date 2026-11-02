<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_berulang', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 80);
            $table->string('kategori', 50);
            $table->integer('jumlah');
            $table->string('aturan', 20)->default('bulanan');
            $table->date('jatuh_tempo')->index();
            $table->string('metode', 30)->nullable();
            $table->string('kantong_tujuan', 30)->nullable();
            $table->string('pencatat', 50);
            $table->boolean('aktif')->default(true);
            $table->date('terakhir_dikonfirmasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_berulang');
    }
};
