<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.demo_mode')) {
            $this->call(DemoSeeder::class);

            return;
        }

        $this->command?->warn('DEMO_MODE=false: tidak ada data contoh yang dimasukkan.');
    }
}
