<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_meta', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 50)->unique();
            $table->string('ikon', 40)->default('tag');
            $table->string('warna', 20)->default('#5F7F8E');
            $table->integer('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_meta');
    }
};
