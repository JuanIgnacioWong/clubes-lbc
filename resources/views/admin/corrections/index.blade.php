@extends('layouts.admin')

@section('title', 'Correcciones | Admin LBC')

@section('content')
<div class="space-y-6">
    <x-card>
        <h1 class="text-2xl font-extrabold">Correcciones seguras</h1>
        <p class="mt-1 text-sm text-slate-600">Genera enlaces controlados por temporada, división y club.</p>

        <form method="POST" action="{{ route('admin.corrections.store') }}" class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            @csrf

            <x-select name="season_id" label="Temporada" required>
                @foreach($seasons as $season)
                    <option value="{{ $season->id }}">{{ $season->year }}</option>
                @endforeach
            </x-select>

            <x-select name="division_id" label="División" required>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }} ({{ $division->season->year }})</option>
                @endforeach
            </x-select>

            <x-select name="club_id" label="Club" required>
                @foreach($clubs as $club)
                    <option value="{{ $club->id }}">{{ $club->name }} ({{ $club->division->name }})</option>
                @endforeach
            </x-select>

            <x-input name="expires_at" label="Expira en" type="datetime-local" />

            <div class="md:col-span-2 lg:col-span-4">
                <x-button type="submit">Generar enlace</x-button>
            </div>
        </form>
    </x-card>

    <x-card>
        <h2 class="text-lg font-bold">Enlaces generados</h2>
        <x-table class="mt-4">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Ruta</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Expira</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($links as $link)
                    @php
                        $url = route('public.correcciones.show', [
                            'year' => $link->season->slug,
                            'division' => $link->division->slug,
                            'club' => $link->club->slug,
                            'token' => $link->token,
                        ]);
                    @endphp
                    <tr class="border-t border-slate-100" x-data="{copied:false}">
                        <td class="px-4 py-3 text-xs">
                            <p class="font-semibold text-slate-700">{{ $link->season->year }} / {{ $link->division->name }} / {{ $link->club->name }}</p>
                            <p class="mt-1 break-all text-slate-500">{{ $url }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $link->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="px-4 py-3">{{ optional($link->expires_at)->format('d/m/Y H:i') ?: 'Sin vencimiento' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded-lg border border-slate-300 px-3 py-1 text-xs" @click="navigator.clipboard.writeText('{{ $url }}'); copied=true; setTimeout(()=>copied=false,1200)">Copiar</button>
                                <form method="POST" action="{{ route('admin.corrections.toggle', $link) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1 text-xs">{{ $link->is_active ? 'Desactivar' : 'Activar' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.corrections.regenerate', $link) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1 text-xs">Regenerar token</button>
                                </form>
                            </div>
                            <p x-show="copied" class="mt-1 text-xs text-emerald-600" style="display:none;">Copiado</p>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        <div class="mt-4">{{ $links->links() }}</div>
    </x-card>
</div>
@endsection
