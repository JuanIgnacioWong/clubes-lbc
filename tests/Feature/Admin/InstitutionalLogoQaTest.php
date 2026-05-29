<?php

namespace Tests\Feature\Admin;

use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionalLogoQaTest extends TestCase
{
    use RefreshDatabase;

    public function test_fallback_is_rendered_when_logo_is_not_configured(): void
    {
        $response = $this->get('/inscripciones');

        $response->assertOk();
        $response->assertSee('rounded-xl bg-brand-700 text-lg font-extrabold text-white', false);
    }

    public function test_admin_can_upload_horizontal_png_with_transparency_and_preview_it(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => $this->createTransparentPngUpload(960, 220),
        ]);

        $response = $this->actingAs($admin)->put('/admin/configuracion', $payload);
        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionDoesntHaveErrors();

        Setting::forgetGlobalCache();
        $logoPath = (string) Setting::getValue('logo_institutional', '');

        $this->assertNotSame('', $logoPath);
        $this->assertStringStartsWith('settings/logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);

        $adminScreen = $this->actingAs($admin)->get('/admin/configuracion');
        $adminScreen->assertOk();
        $adminScreen->assertSee('Preview actual');
        $adminScreen->assertSee('/storage/settings/', false);
        $adminScreen->assertSee('object-contain', false);
    }

    public function test_admin_can_upload_horizontal_webp_logo(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD con soporte WEBP no disponible en este entorno.');
        }

        Storage::fake('public');
        $admin = $this->createAdmin();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => $this->createWebpUpload(1080, 220),
        ]);

        $response = $this->actingAs($admin)->put('/admin/configuracion', $payload);
        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionDoesntHaveErrors();

        Setting::forgetGlobalCache();
        $logoPath = (string) Setting::getValue('logo_institutional', '');

        $this->assertStringEndsWith('.webp', $logoPath);
        $this->assertStringStartsWith('settings/logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_admin_can_upload_jpg_logo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => UploadedFile::fake()->image('platform-logo.jpg', 1200, 240),
        ]);

        $response = $this->actingAs($admin)->put('/admin/configuracion', $payload);
        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionDoesntHaveErrors();

        Setting::forgetGlobalCache();
        $logoPath = (string) Setting::getValue('logo_institutional', '');

        $this->assertStringEndsWith('.jpg', $logoPath);
        $this->assertStringStartsWith('settings/logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_admin_can_upload_svg_logo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => $this->createSvgUpload(),
        ]);

        $response = $this->actingAs($admin)->put('/admin/configuracion', $payload);
        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionDoesntHaveErrors();

        Setting::forgetGlobalCache();
        $logoPath = (string) Setting::getValue('logo_institutional', '');

        $this->assertStringEndsWith('.svg', $logoPath);
        $this->assertStringStartsWith('settings/logos/', $logoPath);
        Storage::disk('public')->assertExists($logoPath);
    }

    public function test_logo_renders_in_public_admin_and_form_views_without_deformation_classes(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        [$season, $division] = $this->createPublicContext();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => $this->createTransparentPngUpload(1600, 240),
        ]);

        $this->actingAs($admin)->put('/admin/configuracion', $payload)->assertRedirect('/admin/configuracion');
        Setting::forgetGlobalCache();

        $publicHome = $this->get('/inscripciones');
        $publicHome->assertOk();
        $publicHome->assertSee('/storage/settings/', false);
        $publicHome->assertSee('object-contain', false);
        $publicHome->assertDontSee('object-cover', false);

        $publicForm = $this->get("/inscripcion/{$season->slug}/{$division->slug}");
        $publicForm->assertOk();
        $publicForm->assertSee('/storage/settings/', false);

        $adminDashboard = $this->actingAs($admin)->get('/admin');
        $adminDashboard->assertOk();
        $adminDashboard->assertSee('/storage/settings/', false);
        $adminDashboard->assertSee('max-w-[140px]', false);
        $adminDashboard->assertSee('max-w-[180px]', false);
        $adminDashboard->assertDontSee('object-cover', false);
    }

    public function test_admin_can_replace_and_remove_logo(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $first = $this->baseSettingsPayload([
            'platform_logo' => $this->createTransparentPngUpload(860, 180, 'first-logo.png'),
        ]);
        $this->actingAs($admin)->put('/admin/configuracion', $first)->assertRedirect('/admin/configuracion');
        Setting::forgetGlobalCache();
        $firstPath = (string) Setting::getValue('logo_institutional', '');
        Storage::disk('public')->assertExists($firstPath);

        $second = $this->baseSettingsPayload([
            'platform_logo' => $this->createTransparentPngUpload(860, 180, 'second-logo.png'),
        ]);
        $this->actingAs($admin)->put('/admin/configuracion', $second)->assertRedirect('/admin/configuracion');
        Setting::forgetGlobalCache();
        $secondPath = (string) Setting::getValue('logo_institutional', '');

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        $remove = $this->baseSettingsPayload([
            'remove_platform_logo' => '1',
        ]);
        $this->actingAs($admin)->put('/admin/configuracion', $remove)->assertRedirect('/admin/configuracion');
        Setting::forgetGlobalCache();

        $this->assertSame('', (string) Setting::getValue('logo_institutional', ''));
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_upload_and_remove_at_the_same_time_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $payload = $this->baseSettingsPayload([
            'platform_logo' => $this->createTransparentPngUpload(900, 200),
            'remove_platform_logo' => '1',
        ]);

        $response = $this->actingAs($admin)
            ->from('/admin/configuracion')
            ->put('/admin/configuracion', $payload);

        $response->assertRedirect('/admin/configuracion');
        $response->assertSessionHasErrors('remove_platform_logo');
    }

    public function test_fallback_is_rendered_if_logo_path_exists_in_settings_but_file_was_deleted(): void
    {
        Storage::fake('public');

        Setting::query()->updateOrCreate(
            ['key' => 'platform_name'],
            ['value' => 'Inscripción de clubes LBC Chile', 'type' => 'string']
        );
        Setting::query()->updateOrCreate(
            ['key' => 'logo_institutional'],
            ['value' => 'settings/deleted-logo.png', 'type' => 'string']
        );
        Setting::forgetGlobalCache();

        $response = $this->get('/inscripciones');

        $response->assertOk();
        $response->assertSee('rounded-xl bg-brand-700 text-lg font-extrabold text-white', false);
        $response->assertDontSee('/storage/settings/deleted-logo.png', false);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
    }

    private function baseSettingsPayload(array $overrides = []): array
    {
        return array_merge([
            'platform_name' => 'Inscripción de clubes LBC Chile',
            'max_logo_mb' => 2,
            'max_documents_mb' => 10,
        ], $overrides);
    }

    private function createPublicContext(): array
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

        Club::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'name' => 'Basket Conce',
            'slug' => 'basket-conce',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$season, $division];
    }

    private function createTransparentPngUpload(int $width, int $height, string $name = 'platform-logo.png'): UploadedFile
    {
        $path = $this->buildTempPath('png');
        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);

        $brand = imagecolorallocate($image, 20, 95, 176);
        imagestring($image, 5, 12, 12, 'LBC Chile', $brand);

        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function createWebpUpload(int $width, int $height, string $name = 'platform-logo.webp'): UploadedFile
    {
        $path = $this->buildTempPath('webp');
        $image = imagecreatetruecolor($width, $height);

        $background = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $background);

        $brand = imagecolorallocate($image, 20, 95, 176);
        imagestring($image, 5, 12, 12, 'LBC Chile', $brand);

        imagewebp($image, $path, 90);
        imagedestroy($image);

        return new UploadedFile($path, $name, 'image/webp', null, true);
    }

    private function createSvgUpload(string $name = 'platform-logo.svg'): UploadedFile
    {
        $path = $this->buildTempPath('svg');
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="240" viewBox="0 0 1200 240">
    <rect width="1200" height="240" fill="#ffffff"/>
    <text x="24" y="140" fill="#145FB0" font-size="84" font-family="Arial, sans-serif">LBC Chile</text>
</svg>
SVG;
        file_put_contents($path, $svg);

        return new UploadedFile($path, $name, 'image/svg+xml', null, true);
    }

    private function buildTempPath(string $extension): string
    {
        $path = tempnam(sys_get_temp_dir(), 'lbc-logo-');

        if ($path === false) {
            $this->fail('No se pudo crear archivo temporal para QA de logo.');
        }

        $target = $path.'.'.$extension;
        rename($path, $target);

        return $target;
    }
}
