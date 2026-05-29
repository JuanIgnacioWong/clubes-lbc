<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'platform_name', 'value' => 'Inscripción de clubes LBC Chile', 'type' => 'string'],
            ['key' => 'logo_institutional', 'value' => '', 'type' => 'string'],
            ['key' => 'inscripciones_intro', 'value' => 'Selecciona tu categoría para comenzar.', 'type' => 'text'],
            ['key' => 'inscripcion_intro', 'value' => 'Completa el formulario con antecedentes oficiales del club.', 'type' => 'text'],
            ['key' => 'inscripcion_success_message', 'value' => 'Recepción completada correctamente.', 'type' => 'text'],
            ['key' => 'roster_template_path', 'value' => '', 'type' => 'string'],
            ['key' => 'roster_template_original_name', 'value' => '', 'type' => 'string'],
            ['key' => 'roster_template_mime', 'value' => '', 'type' => 'string'],
            ['key' => 'roster_template_extension', 'value' => '', 'type' => 'string'],
            ['key' => 'roster_template_is_active', 'value' => '0', 'type' => 'boolean'],
            ['key' => 'roster_template_button_text', 'value' => 'Descargar plantilla', 'type' => 'string'],
            ['key' => 'roster_template_description', 'value' => 'Descarga la plantilla oficial, complétala y súbela en el campo correspondiente.', 'type' => 'string'],
            ['key' => 'roster_template_updated_at', 'value' => '', 'type' => 'datetime'],
            ['key' => 'notification_email', 'value' => 'admin@lbcchile.com', 'type' => 'string'],
            ['key' => 'max_logo_mb', 'value' => '2', 'type' => 'number'],
            ['key' => 'max_documents_mb', 'value' => '10', 'type' => 'number'],
            ['key' => 'allowed_formats', 'value' => 'png,jpg,jpeg,webp,svg,pdf,xls,xlsx,docx', 'type' => 'string'],
            ['key' => 'brand_primary_color', 'value' => '#145FB0', 'type' => 'string'],
            ['key' => 'brand_secondary_color', 'value' => '#2E95F5', 'type' => 'string'],
        ];

        foreach ($settings as $item) {
            Setting::query()->updateOrCreate(['key' => $item['key']], $item);
        }
    }
}
