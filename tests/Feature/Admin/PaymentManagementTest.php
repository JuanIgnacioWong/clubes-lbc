<?php

namespace Tests\Feature\Admin;

use App\Mail\PlatformEventMail;
use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payments_module(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/pagos');

        $response->assertOk();
        $response->assertSee('Gestión de pagos');
    }

    public function test_admin_can_change_payment_status_and_notification_is_sent(): void
    {
        Mail::fake();
        Setting::query()->create(['key' => 'notification_email', 'value' => 'admin-notify@example.com', 'type' => 'string']);

        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        [$submission] = $this->createSubmissionContext();

        $this->actingAs($admin);

        $response = $this->patch("/admin/pagos/{$submission->id}/status", [
            'payment_status' => Submission::PAYMENT_PAID,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'payment_status' => Submission::PAYMENT_PAID,
        ]);

        Mail::assertSent(PlatformEventMail::class, function (PlatformEventMail $mail): bool {
            return str_contains($mail->subjectLine, 'Cambio de estado de pago');
        });
    }

    public function test_admin_can_export_payments_csv(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->createSubmissionContext();

        $this->actingAs($admin);

        $response = $this->get('/admin/pagos/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    private function createSubmissionContext(): array
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

        $submission = Submission::query()->create([
            'season_id' => $season->id,
            'division_id' => $division->id,
            'club_id' => $club->id,
            'responsible_name' => 'Resp Club',
            'phone' => '+56977777777',
            'email' => 'club@example.com',
            'payment_status' => Submission::PAYMENT_IN_REVIEW,
            'submission_status' => Submission::STATUS_RECEIVED,
            'max_allowed_submissions' => 2,
        ]);

        $version = SubmissionVersion::query()->create([
            'submission_id' => $submission->id,
            'version_number' => 1,
            'status' => SubmissionVersion::STATUS_RECEIVED,
            'submitted_at' => now(),
        ]);

        $submission->update(['active_version' => $version->id]);

        return [$submission, $season, $division, $club];
    }
}
