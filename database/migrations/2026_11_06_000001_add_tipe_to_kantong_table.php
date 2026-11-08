<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kantong', function (Blueprint $table) {
            $table->string('tipe', 20)->default('harian')->after('nama')->index();
        });

        DB::table('kantong')->whereNull('tipe')->update(['tipe' => 'harian']);
    }

    public function down(): void
    {
        Schema::table('kantong', function (Blueprint $table) {
            $table->dropIndex(['tipe']);
            $table->dropColumn('tipe');
        });
    }
};
