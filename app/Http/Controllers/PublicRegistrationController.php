<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicSubmissionRequest;
use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Models\Setting;
use App\Services\EventNotificationService;
use App\Services\RosterTemplateService;
use App\Services\SubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicRegistrationController extends Controller
{
    public function index(): View
    {
        $activeSeason = Season::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first() ?? Season::query()->active()->orderByDesc('year')->first();

        $divisions = collect();

        if ($activeSeason) {
            $divisions = Division::query()
                ->where('season_id', $activeSeason->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        }

        return view('public.inscripciones', [
            'activeSeason' => $activeSeason,
            'divisions' => $divisions,
            'introText' => Setting::getValue('inscripciones_intro', 'Selecciona tu categoría para iniciar la carga de antecedentes deportivos oficiales.'),
        ]);
    }

    public function selectDivision(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'season' => ['required', 'string'],
            'division' => ['required', 'string'],
        ]);

        return redirect()->route('public.inscripcion.filtered', [
            'season' => $validated['season'],
            'division' => $validated['division'],
        ]);
    }

    public function fallback(): RedirectResponse
    {
        return redirect()->route('public.inscripciones');
    }

    public function create(string $season, string $division, RosterTemplateService $rosterTemplateService): View
    {
        [$seasonModel, $divisionModel] = $this->resolveSeasonDivision($season, $division);

        $clubs = Club::query()
            ->where('season_id', $seasonModel->id)
            ->where('division_id', $divisionModel->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $rosterTemplate = $rosterTemplateService->getSettings();

        return view('public.inscripcion', [
            'season' => $seasonModel,
            'division' => $divisionModel,
            'clubs' => $clubs,
            'successMessage' => session('success'),
            'introText' => Setting::getValue('inscripcion_intro', 'Completa todos los campos y adjunta los documentos obligatorios.'),
            'rosterTemplateVisible' => $rosterTemplateService->isAvailable($rosterTemplate),
            'rosterTemplateButtonText' => $rosterTemplate['roster_template_button_text'] ?: 'Descargar plantilla',
            'rosterTemplateDescription' => $rosterTemplate['roster_template_description'] ?: 'Descarga la plantilla oficial, complétala y súbela en el campo correspondiente.',
        ]);
    }

    public function store(
        StorePublicSubmissionRequest $request,
        SubmissionService $service,
        EventNotificationService $notifier,
        string $season,
        string $division
    ): RedirectResponse
    {
        [$seasonModel, $divisionModel] = $this->resolveSeasonDivision($season, $division);

        $club = Club::query()
            ->where('id', $request->integer('club_id'))
            ->where('season_id', $seasonModel->id)
            ->where('division_id', $divisionModel->id)
            ->where('is_active', true)
            ->firstOrFail();

        [$submission, $version] = $service->createVersion($seasonModel, $divisionModel, $club, [
            'responsible_name' => $request->string('responsible_name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->string('email')->toString(),
            'club_logo' => $request->file('club_logo'),
            'payment_receipt' => $request->file('payment_receipt'),
            'players_roster' => $request->file('players_roster'),
            'observations' => $request->string('observations')->toString(),
        ]);
        $notifier->submissionReceived($submission->fresh(['season', 'division', 'club']), $version, 'public');

        return redirect()
            ->route('public.inscripcion.filtered', ['season' => $seasonModel->slug, 'division' => $divisionModel->slug])
            ->with('success', Setting::getValue('inscripcion_success_message', sprintf('Inscripción enviada con éxito. Registro #%d versión %d.', $submission->id, $version->version_number)));
    }

    private function resolveSeasonDivision(string $season, string $division): array
    {
        $seasonModel = Season::query()
            ->where('slug', $season)
            ->orWhere('year', $season)
            ->firstOrFail();

        $divisionModel = Division::query()
            ->where('season_id', $seasonModel->id)
            ->where('slug', $division)
            ->where('is_active', true)
            ->firstOrFail();

        return [$seasonModel, $divisionModel];
    }
}
