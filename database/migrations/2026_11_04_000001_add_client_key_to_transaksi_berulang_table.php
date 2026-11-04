<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_berulang', function (Blueprint $table) {
            $table->string('client_key', 64)->nullable()->unique()->after('pencatat');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_berulang', function (Blueprint $table) {
            $table->dropUnique(['client_key']);
            $table->dropColumn('client_key');
        });
    }
};
