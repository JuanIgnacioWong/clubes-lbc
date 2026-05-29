@extends('layouts.admin')

@section('title', 'Pagos | Admin LBC')

@section('content')
<div class="space-y-6" x-data="{ showFilters: true }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-extrabold">Gestión de pagos</h1>
        <div class="flex gap-2">
            <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="showFilters = !showFilters">Mostrar/Ocultar filtros</button>
            <a href="{{ route('admin.pagos.export', request()->query()) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Exportar CSV</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kpi-card title="Total" :value="$stats['total']" />
        <x-kpi-card title="Pendiente" :value="$stats['pending']" />
        <x-kpi-card title="En revisión" :value="$stats['in_review']" />
        <x-kpi-card title="Pagado" :value="$stats['paid']" />
    </div>

    <x-card x-show="showFilters" style="display:none;">
        <form method="GET" class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
            <x-select name="season_id" label="Temporada">
                <option value="">Todas</option>
                @foreach($seasons as $season)
                    <option value="{{ $season->id }}" @selected((string)request('season_id') === (string)$season->id)>{{ $season->year }}</option>
                @endforeach
            </x-select>

            <x-select name="division_id" label="División">
                <option value="">Todas</option>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}" @selected((string)request('division_id') === (string)$division->id)>{{ $division->name }}</option>
                @endforeach
            </x-select>

            <x-select name="club_id" label="Club">
                <option value="">Todos</option>
                @foreach($clubs as $club)
                    <option value="{{ $club->id }}" @selected((string)request('club_id') === (string)$club->id)>{{ $club->name }}</option>
                @endforeach
            </x-select>

            <x-select name="payment_status" label="Estado pago">
                <option value="">Todos</option>
                @foreach(['pending','in_review','paid'] as $st)
                    <option value="{{ $st }}" @selected(request('payment_status')===$st)>{{ $st }}</option>
                @endforeach
            </x-select>

            <x-select name="submission_status" label="Estado envío">
                <option value="">Todos</option>
                @foreach(['received','under_review','accepted','rejected'] as $st)
                    <option value="{{ $st }}" @selected(request('submission_status')===$st)>{{ $st }}</option>
                @endforeach
            </x-select>

            <x-input name="from" label="Desde" type="date" :value="request('from')" />
            <x-input name="to" label="Hasta" type="date" :value="request('to')" />
            <x-input name="q" label="Texto libre" :value="request('q')" placeholder="club, responsable, email" />

            <div class="lg:col-span-4 flex gap-2">
                <x-button type="submit">Filtrar</x-button>
                <a href="{{ route('admin.pagos.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Temporada</th>
                <th class="px-4 py-3">División</th>
                <th class="px-4 py-3">Club</th>
                <th class="px-4 py-3">Responsable</th>
                <th class="px-4 py-3">Envíos</th>
                <th class="px-4 py-3">Estado pago</th>
                <th class="px-4 py-3">Actualización</th>
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($submissions as $submission)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $submission->season->year }}</td>
                    <td class="px-4 py-3">{{ $submission->division->name }}</td>
                    <td class="px-4 py-3">{{ $submission->club->name }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium">{{ $submission->responsible_name }}</p>
                        <p class="text-xs text-slate-500">{{ $submission->email }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $submission->versions->count() }}/{{ $submission->max_allowed_submissions }}</td>
                    <td class="px-4 py-3">
                        @php($badge = $submission->payment_status === 'paid' ? 'success' : ($submission->payment_status === 'in_review' ? 'warning' : 'default'))
                        <x-badge :variant="$badge">{{ $submission->payment_status }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs">{{ $submission->updated_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('admin.pagos.status', $submission) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="payment_status" class="w-full rounded-lg border-slate-300 text-xs">
                                    @foreach(['pending','in_review','paid'] as $st)
                                        <option value="{{ $st }}" @selected($submission->payment_status === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">Guardar</button>
                            </form>
                            <a href="{{ route('admin.submissions.show', $submission) }}" class="inline-flex rounded-lg border border-slate-300 px-2 py-1 text-xs">Ver detalle</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No existen registros para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    {{ $submissions->links() }}
</div>
@endsection
