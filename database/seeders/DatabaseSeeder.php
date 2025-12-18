<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndustrySeeder::class,
            ServiceCategorySeeder::class,
            PackageSeeder::class,
            SettingsSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}