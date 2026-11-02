<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggaran', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->index();
            $table->string('kategori', 50)->nullable();
            $table->integer('jumlah');
            $table->integer('ambang')->default(80);
            $table->timestamps();

            $table->unique(['periode', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggaran');
    }
};
