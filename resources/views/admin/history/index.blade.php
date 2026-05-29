@extends('layouts.admin')

@section('title', 'Historial | Admin LBC')

@section('content')
<div class="space-y-6" x-data="{ showFilters: true }">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Historial y auditoría</h1>
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" @click="showFilters = !showFilters">Mostrar/Ocultar filtros</button>
    </div>

    <x-card x-show="showFilters" style="display:none;">
        <form method="GET" class="grid gap-4 md:grid-cols-3">
            <x-select name="action" label="Acción">
                <option value="">Todas</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </x-select>

            <x-select name="entity_type" label="Entidad">
                <option value="">Todas</option>
                @foreach($entityTypes as $entityType)
                    <option value="{{ $entityType }}" @selected(request('entity_type') === $entityType)>{{ $entityType }}</option>
                @endforeach
            </x-select>

            <x-select name="user_id" label="Usuario">
                <option value="">Todos</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </x-select>

            <x-input name="from" label="Desde" type="date" :value="request('from')" />
            <x-input name="to" label="Hasta" type="date" :value="request('to')" />
            <x-input name="q" label="Texto libre" :value="request('q')" placeholder="acción, descripción..." />

            <div class="md:col-span-3 flex gap-2">
                <x-button type="submit">Filtrar</x-button>
                <a href="{{ route('admin.historial.index') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Fecha</th>
                <th class="px-4 py-3">Usuario</th>
                <th class="px-4 py-3">Acción</th>
                <th class="px-4 py-3">Entidad</th>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Descripción</th>
                <th class="px-4 py-3">IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 text-xs">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->user?->name ?? 'Sistema' }}</td>
                    <td class="px-4 py-3 text-xs font-semibold">{{ $log->action }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->entity_type }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->entity_id }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-xs">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">No hay registros de auditoría para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    {{ $logs->links() }}
</div>
@endsection
