<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\AuditLogger;
use App\Services\RosterTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const DEFAULTS = [
        'platform_name' => 'Inscripción de clubes LBC Chile',
        'logo_institutional' => '',
        'inscripciones_intro' => 'Selecciona tu categoría para comenzar.',
        'inscripcion_intro' => 'Completa el formulario con antecedentes oficiales del club.',
        'inscripcion_success_message' => 'Recepción completada correctamente.',
        'notification_email' => 'admin@lbcchile.com',
        'max_logo_mb' => '2',
        'max_documents_mb' => '10',
        'allowed_formats' => 'png,jpg,jpeg,webp,svg,pdf,xls,xlsx,docx',
        'brand_primary_color' => '#145FB0',
        'brand_secondary_color' => '#2E95F5',
    ];

    public function edit(RosterTemplateService $rosterTemplateService): View
    {
        $values = Setting::globalValues(self::DEFAULTS);

        $rosterTemplate = $rosterTemplateService->getSettings();

        return view('admin.settings.edit', [
            'values' => $values,
            'platformLogoUrl' => Setting::platformLogoUrl(),
            'rosterTemplate' => $rosterTemplate,
            'rosterTemplateAvailable' => $rosterTemplateService->exists($rosterTemplate),
            'rosterTemplateDownloadName' => $rosterTemplateService->publicDownloadName($rosterTemplate),
        ]);
    }

    public function update(UpdateSettingsRequest $request, RosterTemplateService $rosterTemplateService): RedirectResponse
    {
        $values = $request->safe()->except(['platform_logo', 'remove_platform_logo']);
        $settingsToPersist = Setting::globalValues(self::DEFAULTS);
        $settingsToPersist = array_merge($settingsToPersist, $values);
        $settingsToPersist['logo_institutional'] = Setting::normalizePublicDiskPath($settingsToPersist['logo_institutional'] ?? '') ?? '';

        $currentLogoPath = (string) ($settingsToPersist['logo_institutional'] ?? '');
        $uploadedLogo = $request->file('platform_logo');
        $removeLogo = $request->boolean('remove_platform_logo');

        if ($uploadedLogo !== null) {
            $newPath = $uploadedLogo->store('settings/logos', 'public');
            $this->deletePublicLogoIfLocal($currentLogoPath);
            $settingsToPersist['logo_institutional'] = $newPath;

            AuditLogger::log(
                $currentLogoPath !== '' ? 'platform_logo_replaced' : 'platform_logo_uploaded',
                'settings',
                null,
                'Se actualizó el logo institucional.',
                $request
            );
        } elseif ($removeLogo) {
            $this->deletePublicLogoIfLocal($currentLogoPath);
            $settingsToPersist['logo_institutional'] = '';

            AuditLogger::log('platform_logo_removed', 'settings', null, 'Se eliminó el logo institucional.', $request);
        }

        $currentTemplate = $rosterTemplateService->getSettings();

        foreach (self::DEFAULTS as $key => $default) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) ($settingsToPersist[$key] ?? $default), 'type' => $this->resolveType($key)]
            );
        }

        $templateValues = [
            'roster_template_button_text' => (string) ($values['roster_template_button_text'] ?? $currentTemplate['roster_template_button_text']),
            'roster_template_description' => (string) ($values['roster_template_description'] ?? $currentTemplate['roster_template_description']),
        ];

        $uploaded = $request->file('roster_template_file');
        $removing = $request->boolean('remove_roster_template');
        $activationFlag = $request->boolean('roster_template_is_active');

        if ($uploaded !== null) {
            $meta = $rosterTemplateService->storeUploadedTemplate($uploaded, $currentTemplate['roster_template_path']);

            $templateValues = array_merge($templateValues, [
                'roster_template_path' => $meta['path'],
                'roster_template_original_name' => $meta['original_name'],
                'roster_template_mime' => $meta['mime'],
                'roster_template_extension' => $meta['extension'],
                'roster_template_updated_at' => now()->toDateTimeString(),
            ]);

            AuditLogger::log(
                $currentTemplate['roster_template_path'] ? 'roster_template_replaced' : 'roster_template_uploaded',
                'settings',
                null,
                'Se actualizó la plantilla global de nómina de jugadores.',
                $request
            );
        }

        if ($removing) {
            $rosterTemplateService->removeTemplateFile($currentTemplate);

            $templateValues = array_merge($templateValues, [
                'roster_template_path' => '',
                'roster_template_original_name' => '',
                'roster_template_mime' => '',
                'roster_template_extension' => '',
                'roster_template_is_active' => '0',
                'roster_template_updated_at' => now()->toDateTimeString(),
            ]);

            AuditLogger::log('roster_template_removed', 'settings', null, 'Se actualizó la plantilla global de nómina de jugadores.', $request);
        } else {
            $templateValues['roster_template_is_active'] = $activationFlag ? '1' : '0';

            if ((bool) $rosterTemplateService->isActive($currentTemplate) !== $activationFlag) {
                AuditLogger::log(
                    $activationFlag ? 'roster_template_activated' : 'roster_template_deactivated',
                    'settings',
                    null,
                    'Se actualizó la plantilla global de nómina de jugadores.',
                    $request
                );
            }
        }

        $rosterTemplateService->persist($templateValues);
        Setting::forgetGlobalCache();

        AuditLogger::log('settings_updated', 'settings', null, 'Configuración global actualizada.', $request);

        return redirect()->route('admin.configuracion.edit')->with('success', 'Configuración actualizada.');
    }

    private function resolveType(string $key): string
    {
        return match ($key) {
            'max_logo_mb', 'max_documents_mb' => 'number',
            default => 'string',
        };
    }

    private function deletePublicLogoIfLocal(string $logoPath): void
    {
        $normalizedPath = Setting::normalizePublicDiskPath($logoPath);

        if ($normalizedPath === null) {
            return;
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }
}
