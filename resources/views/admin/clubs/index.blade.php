@extends('layouts.admin')

@section('title', 'Clubes | Admin LBC')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Clubes</h1>
        <a href="{{ route('admin.clubs.create') }}" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Nuevo club</a>
    </div>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Temporada</th><th class="px-4 py-3">División</th><th class="px-4 py-3">Club</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody>
            @foreach($clubs as $club)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $club->season->year }}</td>
                    <td class="px-4 py-3">{{ $club->division->name }}</td>
                    <td class="px-4 py-3">{{ $club->name }}</td>
                    <td class="px-4 py-3">{{ $club->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('admin.clubs.edit', $club) }}" class="text-brand-700">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>

    {{ $clubs->links() }}
</div>
@endsection
