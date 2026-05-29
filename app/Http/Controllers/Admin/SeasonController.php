<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeasonRequest;
use App\Models\Season;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function index(): View
    {
        $seasons = Season::query()->orderByDesc('year')->paginate(20);

        return view('admin.seasons.index', compact('seasons'));
    }

    public function create(): View
    {
        return view('admin.seasons.create');
    }

    public function store(SeasonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Season::query()->update(['is_default' => false]);
        }

        $season = Season::query()->create($data);
        AuditLogger::log('season_created', 'season', $season, 'Temporada creada.', $request);

        return redirect()->route('admin.seasons.index')->with('success', 'Temporada creada.');
    }

    public function edit(Season $season): View
    {
        return view('admin.seasons.edit', compact('season'));
    }

    public function update(SeasonRequest $request, Season $season): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            Season::query()->where('id', '!=', $season->id)->update(['is_default' => false]);
        }

        $season->update($data);
        AuditLogger::log('season_updated', 'season', $season, 'Temporada actualizada.', $request);

        return redirect()->route('admin.seasons.index')->with('success', 'Temporada actualizada.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        $season->delete();

        return redirect()->route('admin.seasons.index')->with('success', 'Temporada eliminada.');
    }
}
