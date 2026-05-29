<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\CorrectionLink;
use App\Models\Division;
use App\Models\Season;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CorrectionLinkController extends Controller
{
    public function index(): View
    {
        $seasons = Season::query()->orderByDesc('year')->get();
        $divisions = Division::query()->with('season')->orderBy('name')->get();
        $clubs = Club::query()->with(['season', 'division'])->orderBy('name')->get();

        $links = CorrectionLink::query()
            ->with(['season', 'division', 'club'])
            ->latest('created_at')
            ->paginate(30);

        return view('admin.corrections.index', compact('seasons', 'divisions', 'clubs', 'links'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'season_id' => ['required', 'integer', 'exists:seasons,id'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $link = CorrectionLink::query()->create([
            ...$data,
            'token' => Str::random(64),
            'is_active' => true,
        ]);

        AuditLogger::log('correction_link_generated', 'correction_link', $link, 'Enlace de corrección creado.', $request);

        return redirect()->route('admin.corrections.index')->with('success', 'Enlace generado correctamente.');
    }

    public function toggle(Request $request, CorrectionLink $correctionLink): RedirectResponse
    {
        $correctionLink->update(['is_active' => ! $correctionLink->is_active]);

        AuditLogger::log('correction_link_toggled', 'correction_link', $correctionLink, 'Estado del enlace de corrección actualizado.', $request);

        return redirect()->back()->with('success', 'Estado del enlace actualizado.');
    }

    public function regenerate(Request $request, CorrectionLink $correctionLink): RedirectResponse
    {
        $correctionLink->update([
            'token' => Str::random(64),
            'used_at' => null,
        ]);

        AuditLogger::log('correction_link_regenerated', 'correction_link', $correctionLink, 'Token regenerado.', $request);

        return redirect()->back()->with('success', 'Token regenerado.');
    }
}
