<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('year', 2026)->firstOrFail();
        $divisions = Division::query()->where('season_id', $season->id)->get()->keyBy('slug');

        $rows = [
            ['division' => 'plus-37', 'name' => 'Basket Conce', 'slug' => 'basket-conce'],
            ['division' => 'plus-37', 'name' => 'Leones del Sur', 'slug' => 'leones-del-sur'],
            ['division' => 'plus-45', 'name' => 'Titanes LBC', 'slug' => 'titanes-lbc'],
            ['division' => 'plus-45', 'name' => 'Club Pacífico', 'slug' => 'club-pacifico'],
            ['division' => 'primera-division', 'name' => 'Atlético Centro', 'slug' => 'atletico-centro'],
            ['division' => 'segunda-division', 'name' => 'Básquet Andino', 'slug' => 'basquet-andino'],
        ];

        foreach ($rows as $idx => $row) {
            $division = $divisions[$row['division']] ?? null;

            if (! $division) {
                continue;
            }

            Club::query()->updateOrCreate(
                ['season_id' => $season->id, 'division_id' => $division->id, 'slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'is_active' => true,
                    'sort_order' => $idx + 1,
                ]
            );
        }
    }
}
