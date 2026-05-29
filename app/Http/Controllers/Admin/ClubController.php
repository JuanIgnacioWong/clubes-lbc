<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClubRequest;
use App\Models\Club;
use App\Models\Division;
use App\Models\Season;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $clubs = Club::query()->with(['season', 'division'])->orderBy('season_id')->orderBy('division_id')->orderBy('sort_order')->paginate(25);

        return view('admin.clubs.index', compact('clubs'));
    }

    public function create(): View
    {
        $seasons = Season::query()->orderByDesc('year')->get();
        $divisions = Division::query()->with('season')->orderBy('name')->get();

        return view('admin.clubs.create', compact('seasons', 'divisions'));
    }

    public function store(ClubRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $club = Club::query()->create($data);
        AuditLogger::log('club_created', 'club', $club, 'Club creado.', $request);

        return redirect()->route('admin.clubs.index')->with('success', 'Club creado.');
    }

    public function edit(Club $club): View
    {
        $seasons = Season::query()->orderByDesc('year')->get();
        $divisions = Division::query()->with('season')->orderBy('name')->get();

        return view('admin.clubs.edit', compact('club', 'seasons', 'divisions'));
    }

    public function update(ClubRequest $request, Club $club): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $club->update($data);
        AuditLogger::log('club_updated', 'club', $club, 'Club actualizado.', $request);

        return redirect()->route('admin.clubs.index')->with('success', 'Club actualizado.');
    }

    public function destroy(Club $club): RedirectResponse
    {
        $club->delete();

        return redirect()->route('admin.clubs.index')->with('success', 'Club eliminado.');
    }
}
