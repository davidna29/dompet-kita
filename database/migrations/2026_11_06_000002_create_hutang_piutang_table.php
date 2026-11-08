<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hutang_piutang', function (Blueprint $table) {
            $table->id();
            $table->string('pihak', 50);
            $table->string('jenis', 10);
            $table->integer('nominal');
            $table->integer('sisa');
            $table->date('tanggal')->index();
            $table->date('jatuh_tempo')->nullable();
            $table->string('status', 10)->default('belum');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['jenis', 'status', 'jatuh_tempo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hutang_piutang');
    }
};
