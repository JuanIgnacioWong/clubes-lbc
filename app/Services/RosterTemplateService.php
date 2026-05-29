<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RosterTemplateService
{
    private const SETTINGS = [
        'roster_template_path',
        'roster_template_original_name',
        'roster_template_mime',
        'roster_template_extension',
        'roster_template_is_active',
        'roster_template_button_text',
        'roster_template_description',
        'roster_template_updated_at',
    ];

    private const DEFAULTS = [
        'roster_template_path' => '',
        'roster_template_original_name' => '',
        'roster_template_mime' => '',
        'roster_template_extension' => '',
        'roster_template_is_active' => '0',
        'roster_template_button_text' => 'Descargar plantilla',
        'roster_template_description' => 'Descarga la plantilla oficial, complétala y súbela en el campo correspondiente.',
        'roster_template_updated_at' => '',
    ];

    public function getSettings(): array
    {
        $values = [];

        foreach (self::DEFAULTS as $key => $default) {
            $values[$key] = Setting::getValue($key, $default);
        }

        return $values;
    }

    public function isActive(array $settings): bool
    {
        return filter_var($settings['roster_template_is_active'] ?? '0', FILTER_VALIDATE_BOOL);
    }

    public function exists(array $settings): bool
    {
        $path = $settings['roster_template_path'] ?? '';

        if ($path === '') {
            return false;
        }

        return Storage::disk('private')->exists($path);
    }

    public function isAvailable(array $settings): bool
    {
        return $this->isActive($settings) && $this->exists($settings);
    }

    public function publicDownloadName(array $settings): string
    {
        $ext = strtolower((string) ($settings['roster_template_extension'] ?? ''));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'pdf';

        return 'plantilla-jugadores-CCCP-2026.'.$ext;
    }

    /**
     * @return array{path:string,original_name:string,mime:string,extension:string}
     */
    public function storeUploadedTemplate(UploadedFile $file, ?string $previousPath = null): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $internalName = sprintf(
            'roster-template-%s-%s.%s',
            now()->format('YmdHis'),
            Str::lower(Str::random(6)),
            $extension
        );

        $path = Storage::disk('private')->putFileAs('templates/roster', $file, $internalName);

        if ($previousPath !== null && $previousPath !== '' && $previousPath !== $path) {
            Storage::disk('private')->delete($previousPath);
        }

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => (string) $file->getMimeType(),
            'extension' => $extension,
        ];
    }

    public function removeTemplateFile(array $settings): void
    {
        $path = $settings['roster_template_path'] ?? '';

        if ($path !== '' && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }

    /**
     * @param array<string,string|int|bool|null> $values
     */
    public function persist(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! in_array($key, self::SETTINGS, true)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) ($value ?? ''),
                    'type' => $this->resolveType($key),
                ]
            );
        }
    }

    private function resolveType(string $key): string
    {
        return match ($key) {
            'roster_template_is_active' => 'boolean',
            'roster_template_updated_at' => 'datetime',
            default => 'string',
        };
    }
}
