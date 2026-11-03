<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            MasterDataSeeder::class,
            AdminAwalSeeder::class,
        ]);

        // Data contoh tidak pernah dimuat di produksi.
        if (! app()->isProduction()) {
            $this->call(DataContohSeeder::class);
        }
    }
}
