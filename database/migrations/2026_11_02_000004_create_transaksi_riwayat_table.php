<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_riwayat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_id')->index();
            $table->string('aksi', 20);
            $table->string('oleh', 50)->nullable();
            $table->string('sumber', 20);
            $table->json('sebelum')->nullable();
            $table->json('sesudah')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_riwayat');
    }
};
