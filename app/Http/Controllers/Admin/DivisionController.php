<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DivisionRequest;
use App\Models\Division;
use App\Models\Season;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(): View
    {
        $divisions = Division::query()->with('season')->orderBy('season_id')->orderBy('sort_order')->paginate(25);

        return view('admin.divisions.index', compact('divisions'));
    }

    public function create(): View
    {
        $seasons = Season::query()->orderByDesc('year')->get();

        return view('admin.divisions.create', compact('seasons'));
    }

    public function store(DivisionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['payment_is_active'] = $request->boolean('payment_is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $division = Division::query()->create($data);

        AuditLogger::log('division_created', 'division', $division, 'División creada.', $request);
        AuditLogger::log('division_payment_updated', 'division', $division, 'Configuración de pago actualizada.', $request);

        return redirect()->route('admin.divisions.index')->with('success', 'División creada.');
    }

    public function edit(Division $division): View
    {
        $seasons = Season::query()->orderByDesc('year')->get();

        return view('admin.divisions.edit', compact('division', 'seasons'));
    }

    public function update(DivisionRequest $request, Division $division): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['payment_is_active'] = $request->boolean('payment_is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $division->update($data);

        AuditLogger::log('division_updated', 'division', $division, 'División actualizada.', $request);
        AuditLogger::log('division_payment_updated', 'division', $division, 'Configuración de pago actualizada.', $request);

        return redirect()->route('admin.divisions.index')->with('success', 'División actualizada.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        $division->delete();

        return redirect()->route('admin.divisions.index')->with('success', 'División eliminada.');
    }
}
