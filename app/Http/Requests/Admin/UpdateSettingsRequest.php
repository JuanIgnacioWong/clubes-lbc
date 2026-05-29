<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform_name' => ['required', 'string', 'max:180'],
            'inscripciones_intro' => ['nullable', 'string', 'max:1000'],
            'inscripcion_intro' => ['nullable', 'string', 'max:1000'],
            'inscripcion_success_message' => ['nullable', 'string', 'max:500'],
            'notification_email' => ['nullable', 'email:rfc', 'max:150'],
            'max_logo_mb' => ['required', 'integer', 'between:1,10'],
            'max_documents_mb' => ['required', 'integer', 'between:1,20'],
            'allowed_formats' => ['nullable', 'string', 'max:255'],
            'brand_primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'brand_secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'platform_logo' => [
                'nullable',
                'file',
                'max:2048',
                'mimes:png,jpg,jpeg,webp,svg',
            ],
            'remove_platform_logo' => ['nullable', 'boolean'],

            'roster_template_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,xls,xlsx',
                'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'roster_template_is_active' => ['nullable', 'boolean'],
            'roster_template_button_text' => ['nullable', 'string', 'max:120'],
            'roster_template_description' => ['nullable', 'string', 'max:500'],
            'remove_roster_template' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $activating = $this->boolean('roster_template_is_active');
            $removing = $this->boolean('remove_roster_template');
            $uploading = $this->hasFile('roster_template_file');

            $currentPath = (string) Setting::getValue('roster_template_path', '');
            $currentExists = $currentPath !== '' && Storage::disk('private')->exists($currentPath);

            if ($activating && ! $uploading && ! $currentExists) {
                $validator->errors()->add('roster_template_is_active', 'No puedes activar la plantilla si no existe un archivo válido cargado.');
            }

            if ($activating && $removing) {
                $validator->errors()->add('remove_roster_template', 'No puedes activar y eliminar la plantilla al mismo tiempo.');
            }

            if ($this->boolean('remove_platform_logo') && $this->hasFile('platform_logo')) {
                $validator->errors()->add('remove_platform_logo', 'No puedes reemplazar y eliminar el logo institucional al mismo tiempo.');
            }
        });
    }
}
