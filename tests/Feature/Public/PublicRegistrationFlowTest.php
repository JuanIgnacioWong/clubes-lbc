<?php

namespace Tests\Feature\Public;

use App\Mail\PlatformEventMail;
use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inscripciones_shows_only_active_divisions(): void
    {
        $season = Season::query()->create([
            'year' => 2026,
            'name' => 'Temporada Oficial 2026',
            'slug' => '2026',
            'is_active' => true,
            'is_default' => true,
        ]);

        Division::query()->create([
            'season_id' => $season->id,
            'name' => '+37',
            'slug' => 'plus-37',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Division::query()->create([
            'season_id' => $season->id,
            'name' => '+99',
            'slug' => 'plus-99',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get('/inscripciones');

        $response->assertOk();
        $response->assertSee('+37');
        $response->assertDontSee('+99');
    }

    public function test_public_submission_creates_submission_and_first_version(): void
    {
        Storage::fake('private');
        Mail::fake();
        Setting::query()->create(['key' => 'notification_email', 'value' => 'admin-notify@example.com', 'type' => 'string']);
        [$season, $division, $club] = $this->createBaseContext();

        $response = $this->post("/inscripcion/{$season->slug}/{$division->slug}", [
            'club_id' => $club->id,
            'responsible_name' => 'Juan Perez',
            'phone' => '+56999999999',
            'email' => 'juan@example.com',
            'club_logo' => UploadedFile::fake()->image('logo.png', 120, 120),
            'payment_receipt' => UploadedFile::fake()->create('comprobante.pdf', 120, 'application/pdf'),
            'players_roster' => UploadedFile::fake()->create('nomina.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'observations' => 'Entrega inicial.',
        ]);

        $response->assertRedirect("/inscripcion/{$season->slug}/{$division->slug}");

        $submission = Submission::query()->where('club_id', $club->id)->first();

        $this->assertNotNull($submission);
        $this->assertSame(Submission::PAYMENT_IN_REVIEW, $submission->payment_status);
        $this->assertSame(1, $submission->versions()->count());

        $version = SubmissionVersion::query()->where('submission_id', $submission->id)->first();
        $this->assertNotNull($version);
        $this->assertNotNull($version->payment_receipt_path);
        $this->assertTrue(Storage::disk('private')->exists($version->payment_receipt_path));
        Mail::assertSent(PlatformEventMail::class);
    }

    public function test_third_submission_is_blocked_by_default_limit(): void
    {
        Storage::fake('private');
        [$season, $division, $club] = $this->createBaseContext();

        $this->submitPayload($season->slug, $division->slug, $club->id);
        $this->submitPayload($season->slug, $division->slug, $club->id);

        $response = $this->submitPayload($season->slug, $division->slug, $club->id);

        $response->assertSessionHasErrors('club_id');

        $submission = Submission::query()->where('club_id', $club->id)->firstOrFail();
        $this->assertSame(2, $submission->versions()->count());
    }

    private function submitPayload(string $seasonSlug, string $divisionSlug, int $clubId)
    {
        return $this->from("/inscripcion/{$seasonSlug}/{$divisionSlug}")->post("/inscripcion/{$seasonSlug}/{$divisionSlug}", [
            'club_id' => $clubId,
            'responsible_name' => 'Responsable Club',
            'phone' => '+56911111111',
            'email' => 'club@example.com',
            'club_logo' => UploadedFile::fake()->image('logo.png', 80, 80),
            'payment_receipt' => UploadedFile::fake()->create('comprobante.pdf', 120, 'application/pdf'),
            'players_roster' => UploadedFile::fake()->create('nomina.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'observations' => 'Envio',
        ]);
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
