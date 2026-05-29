<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Season;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->where('year', 2026)->firstOrFail();

        $items = [
            [
                'name' => '+37',
                'slug' => 'plus-37',
                'sort_order' => 1,
                'payment_url' => 'https://www.webpay.cl/form-pay/plus-37',
                'payment_button_text' => 'Pagar +37',
                'payment_description' => 'Pago oficial inscripción categoría +37.',
                'payment_is_active' => true,
            ],
            [
                'name' => '+45',
                'slug' => 'plus-45',
                'sort_order' => 2,
                'payment_url' => 'https://www.webpay.cl/form-pay/plus-45',
                'payment_button_text' => 'Pagar +45',
                'payment_description' => 'Pago oficial inscripción categoría +45.',
                'payment_is_active' => true,
            ],
            [
                'name' => 'Primera División',
                'slug' => 'primera-division',
                'sort_order' => 3,
                'payment_url' => null,
                'payment_button_text' => null,
                'payment_description' => null,
                'payment_is_active' => false,
            ],
            [
                'name' => 'Segunda División',
                'slug' => 'segunda-division',
                'sort_order' => 4,
                'payment_url' => null,
                'payment_button_text' => null,
                'payment_description' => null,
                'payment_is_active' => false,
            ],
        ];

        foreach ($items as $item) {
            Division::query()->updateOrCreate(
                ['season_id' => $season->id, 'slug' => $item['slug']],
                [
                    'season_id' => $season->id,
                    'name' => $item['name'],
                    'description' => 'Categoría oficial temporada 2026',
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                    'payment_url' => $item['payment_url'],
                    'payment_button_text' => $item['payment_button_text'],
                    'payment_description' => $item['payment_description'],
                    'payment_is_active' => $item['payment_is_active'],
                ]
            );
        }
    }
}
