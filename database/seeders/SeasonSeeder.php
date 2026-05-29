<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::query()->updateOrCreate(
            ['year' => 2026],
            [
                'name' => 'Temporada Oficial 2026',
                'slug' => '2026',
                'is_active' => true,
                'is_default' => true,
            ]
        );
    }
}
