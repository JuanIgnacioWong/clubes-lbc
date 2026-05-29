@extends('layouts.admin')

@section('title', 'Versiones | Admin LBC')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-2">
        <h1 class="text-2xl font-extrabold">Versiones</h1>
        <a href="{{ route('admin.submissions.index') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Ver submissions</a>
    </div>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">ID</th>
                <th class="px-4 py-3">Temporada</th>
                <th class="px-4 py-3">División</th>
                <th class="px-4 py-3">Club</th>
                <th class="px-4 py-3">Versión</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Actualizado</th>
                <th class="px-4 py-3">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($versions as $version)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $version->id }}</td>
                    <td class="px-4 py-3">{{ $version->submission?->season?->year }}</td>
                    <td class="px-4 py-3">{{ $version->submission?->division?->name }}</td>
                    <td class="px-4 py-3">{{ $version->submission?->club?->name }}</td>
                    <td class="px-4 py-3">{{ $version->version_number }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.versions.update-status', $version) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="w-full rounded-lg border-slate-300 text-xs">
                                @foreach(['received','under_review','accepted','rejected','replaced'] as $status)
                                    <option value="{{ $status }}" @selected($version->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">Guardar</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ optional($version->updated_at)->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            @if($version->submission)
                                <a href="{{ route('admin.submissions.show', $version->submission) }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">Detalle</a>
                            @endif
                            <form method="POST" action="{{ route('admin.versions.destroy', $version) }}" onsubmit="return confirm('¿Eliminar versión?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-rose-300 px-2 py-1 text-xs text-rose-700">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">No existen versiones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    {{ $versions->links() }}
</div>
@endsection
