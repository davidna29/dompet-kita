<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantong', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 30)->unique();
            $table->integer('saldo_awal')->default(0);
            $table->date('tanggal_saldo_awal');
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantong');
    }
};
