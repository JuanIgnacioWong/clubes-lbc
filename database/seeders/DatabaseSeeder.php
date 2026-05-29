<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SeasonSeeder::class,
            DivisionSeeder::class,
            ClubSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
