<?php

namespace Tests\Feature\Public;

use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RosterTemplateFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_pdf_template_and_public_download_has_controlled_name(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->put('/admin/configuracion', $this->settingsPayload([
            'roster_template_is_active' => '1',
            'roster_template_file' => UploadedFile::fake()->create('mi-plantilla-v1.pdf', 120, 'application/pdf'),
        ]));

        $response->assertRedirect('/admin/configuracion');

        $path = Setting::getValue('roster_template_path');
        $this->assertNotEmpty($path);
        $this->assertTrue(Storage::disk('private')->exists($path));

        $download = $this->get('/plantilla-nomina-jugadores');

        $download->assertOk();
        $download->assertHeader('content-disposition', 'attachment; filename=plantilla-jugadores-CCCP-2026.pdf');
        $this->assertSame(0, Submission::query()->count());
    }

    public function test_replacing_template_changes_download_extension_and_removes_previous_file(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->put('/admin/configuracion', $this->settingsPayload([
            'roster_template_is_active' => '1',
            'roster_template_file' => UploadedFile::fake()->create('uno.pdf', 100, 'application/pdf'),
        ]));

        $previousPath = Setting::getValue('roster_template_path');
        $this->assertNotEmpty($previousPath);

        $this->put('/admin/configuracion', $this->settingsPayload([
            'roster_template_is_active' => '1',
            'roster_template_file' => UploadedFile::fake()->create(
                'dos.docx',
                120,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ),
        ]))->assertRedirect('/admin/configuracion');

        $currentPath = Setting::getValue('roster_template_path');
        $this->assertNotSame($previousPath, $currentPath);
        $this->assertFalse(Storage::disk('private')->exists($previousPath));
        $this->assertTrue(Storage::disk('private')->exists($currentPath));

        $download = $this->get('/plantilla-nomina-jugadores');
        $download->assertHeader('content-disposition', 'attachment; filename=plantilla-jugadores-CCCP-2026.docx');
    }

    public function test_deactivated_template_is_hidden_in_public_form_and_download_returns_404(): void
    {
        Storage::fake('private');
        [$season, $division, $club] = $this->createBaseContext();

        $path = 'templates/roster/template.pdf';
        Storage::disk('private')->put($path, 'contenido');

        Setting::query()->updateOrCreate(['key' => 'roster_template_path'], ['value' => $path, 'type' => 'string']);
        Setting::query()->updateOrCreate(['key' => 'roster_template_extension'], ['value' => 'pdf', 'type' => 'string']);
        Setting::query()->updateOrCreate(['key' => 'roster_template_is_active'], ['value' => '0', 'type' => 'boolean']);
        Setting::query()->updateOrCreate(['key' => 'roster_template_button_text'], ['value' => 'Descargar plantilla', 'type' => 'string']);
        Setting::query()->updateOrCreate(['key' => 'roster_template_description'], ['value' => 'Texto', 'type' => 'string']);

        $form = $this->get("/inscripcion/{$season->slug}/{$division->slug}");
        $form->assertOk();
        $form->assertDontSee('Plantilla de nómina de jugadores');
        $form->assertSee('https://example.com/pago');

        $download = $this->get('/plantilla-nomina-jugadores');
        $download->assertNotFound();

        $this->assertSame(0, Submission::query()->count());
    }

    public function test_invalid_template_file_upload_is_blocked(): void
    {
        Storage::fake('private');

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->from('/admin/configuracion')->put('/admin/configuracion', $this->settingsPayload([
            'roster_template_file' => UploadedFile::fake()->create('malicioso.zip', 120, 'application/zip'),
        ]));

        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionHasErrors('roster_template_file');
    }

    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'platform_name' => 'Inscripción de clubes LBC Chile',
            'logo_institutional' => 'https://example.com/logo.png',
            'inscripciones_intro' => 'Intro A',
            'inscripcion_intro' => 'Intro B',
            'inscripcion_success_message' => 'OK',
            'notification_email' => 'admin@lbcchile.com',
            'max_logo_mb' => '2',
            'max_documents_mb' => '10',
            'allowed_formats' => 'png,jpg,jpeg,webp,svg,pdf,xls,xlsx,docx',
            'brand_primary_color' => '#145FB0',
            'brand_secondary_color' => '#2E95F5',
            'roster_template_button_text' => 'Descargar plantilla',
            'roster_template_description' => 'Descarga la plantilla oficial, complétala y súbela en el campo correspondiente.',
        ], $overrides);
    }

    private function createBaseContext(): array
    {
        $season = Season::query()->create([
            'year' => 2026,
            'name' => 'Temporada Oficial 2026',
            'slug' => '2026',
            'is_active' => true,
            'is_default' => true,
        ]);

        $division = Division::query()->create([
            'season_id' => $season->id,
            'name' => '+37',
            'slug' => 'plus-37',
            'is_active' => true,
            'sort_order' => 1,
            'payment_url' => 'https://example.com/pago',
            'payment_is_active' => true,
        ]);

        $club = Club::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'name' => 'Basket Conce',
            'slug' => 'basket-conce',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$season, $division, $club];
    }
}
