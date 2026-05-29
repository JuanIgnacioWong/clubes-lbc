<?php

namespace Tests\Feature\Public;

use App\Mail\PlatformEventMail;
use App\Models\Club;
use App\Models\CorrectionLink;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CorrectionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_correction_link_creates_new_version(): void
    {
        Storage::fake('private');
        Mail::fake();
        Setting::query()->create(['key' => 'notification_email', 'value' => 'admin-notify@example.com', 'type' => 'string']);
        [$season, $division, $club] = $this->createBaseContext();
        $submission = $this->createSubmissionWithVersions($season, $division, $club, 1);

        $link = CorrectionLink::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'club_id' => $club->id,
            'token' => Str::random(64),
            'is_active' => true,
        ]);

        $url = "/correcciones/{$season->slug}/{$division->slug}/{$club->slug}/{$link->token}";

        $response = $this->post($url, [
            'responsible_name' => 'Encargado Corrección',
            'phone' => '+56922222222',
            'email' => 'corr@example.com',
            'players_roster' => UploadedFile::fake()->create('nomina-corregida.xlsx', 120, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'observations' => 'Corrección de nómina',
        ]);

        $response->assertRedirect();

        $submission->refresh();
        $this->assertSame(2, $submission->versions()->count());
        $this->assertNotNull($link->fresh()->used_at);
        Mail::assertSent(PlatformEventMail::class);
    }

    public function test_inactive_correction_link_is_forbidden(): void
    {
        [$season, $division, $club] = $this->createBaseContext();

        $link = CorrectionLink::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'club_id' => $club->id,
            'token' => Str::random(64),
            'is_active' => false,
        ]);

        $response = $this->get("/correcciones/{$season->slug}/{$division->slug}/{$club->slug}/{$link->token}");

        $response->assertForbidden();
    }

    public function test_correction_is_blocked_without_available_capacity(): void
    {
        [$season, $division, $club] = $this->createBaseContext();
        $this->createSubmissionWithVersions($season, $division, $club, 2, 2);

        $link = CorrectionLink::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'club_id' => $club->id,
            'token' => Str::random(64),
            'is_active' => true,
        ]);

        $response = $this->get("/correcciones/{$season->slug}/{$division->slug}/{$club->slug}/{$link->token}");

        $response->assertForbidden();
    }

    private function createSubmissionWithVersions(Season $season, Division $division, Club $club, int $versions, int $maxAllowed = 2): Submission
    {
        $submission = Submission::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'club_id' => $club->id,
            'responsible_name' => 'Responsable',
            'phone' => '+56912312312',
            'email' => 'resp@example.com',
            'payment_status' => Submission::PAYMENT_IN_REVIEW,
            'submission_status' => Submission::STATUS_RECEIVED,
            'max_allowed_submissions' => $maxAllowed,
        ]);

        $lastVersionId = null;

        for ($i = 1; $i <= $versions; $i++) {
            $version = SubmissionVersion::query()->create([
                'submission_id' => $submission->id,
                'version_number' => $i,
                'status' => SubmissionVersion::STATUS_RECEIVED,
                'submitted_at' => now(),
            ]);

            $lastVersionId = $version->id;
        }

        $submission->update(['active_version' => $lastVersionId]);

        return $submission;
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
