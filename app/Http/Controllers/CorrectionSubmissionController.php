<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorrectionSubmissionRequest;
use App\Models\Club;
use App\Models\CorrectionLink;
use App\Models\Division;
use App\Models\Season;
use App\Models\Submission;
use App\Services\EventNotificationService;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CorrectionSubmissionController extends Controller
{
    public function show(string $year, string $division, string $club, string $token): View
    {
        [$seasonModel, $divisionModel, $clubModel, $link] = $this->resolveContext($year, $division, $club, $token);

        abort_unless($link->isUsable(), 403, 'El enlace de corrección no está disponible.');
        abort_unless($this->hasCapacity($seasonModel, $divisionModel, $clubModel), 403, 'El club no tiene cupo disponible para más correcciones.');

        return view('public.correccion', [
            'season' => $seasonModel,
            'division' => $divisionModel,
            'club' => $clubModel,
            'link' => $link,
        ]);
    }

    public function store(
        StoreCorrectionSubmissionRequest $request,
        SubmissionService $service,
        EventNotificationService $notifier,
        string $year,
        string $division,
        string $club,
        string $token
    ): RedirectResponse {
        [$seasonModel, $divisionModel, $clubModel, $link] = $this->resolveContext($year, $division, $club, $token);

        abort_unless($link->isUsable(), 403, 'El enlace de corrección no está disponible.');
        abort_unless($this->hasCapacity($seasonModel, $divisionModel, $clubModel), 403, 'El club no tiene cupo disponible para más correcciones.');

        [$submission, $version] = $service->createVersion($seasonModel, $divisionModel, $clubModel, [
            'responsible_name' => $request->string('responsible_name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->string('email')->toString(),
            'club_logo' => $request->file('club_logo'),
            'payment_receipt' => $request->file('payment_receipt'),
            'players_roster' => $request->file('players_roster'),
            'observations' => $request->string('observations')->toString(),
        ]);
        $notifier->submissionReceived($submission->fresh(['season', 'division', 'club']), $version, 'correction');

        $link->forceFill(['used_at' => now()])->save();

        return redirect()->back()->with('success', 'Corrección enviada correctamente.');
    }

    private function resolveContext(string $year, string $division, string $club, string $token): array
    {
        $seasonModel = Season::query()->where('slug', $year)->orWhere('year', $year)->firstOrFail();

        $divisionModel = Division::query()
            ->where('season_id', $seasonModel->id)
            ->where('slug', $division)
            ->firstOrFail();

        $clubModel = Club::query()
            ->where('season_id', $seasonModel->id)
            ->where('division_id', $divisionModel->id)
            ->where('slug', $club)
            ->firstOrFail();

        $link = CorrectionLink::query()
            ->where('season_id', $seasonModel->id)
            ->where('division_id', $divisionModel->id)
            ->where('club_id', $clubModel->id)
            ->where('token', $token)
            ->firstOrFail();

        return [$seasonModel, $divisionModel, $clubModel, $link];
    }

    private function hasCapacity(Season $season, Division $division, Club $club): bool
    {
        $submission = Submission::query()
            ->where('season_id', $season->id)
            ->where('division_id', $division->id)
            ->where('club_id', $club->id)
            ->first();

        if (! $submission) {
            return true;
        }

        return $submission->versions()->count() < $submission->max_allowed_submissions;
    }
}
