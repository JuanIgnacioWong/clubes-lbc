@extends('layouts.admin')

@section('title', 'Temporadas | Admin LBC')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Temporadas</h1>
        <a href="{{ route('admin.seasons.create') }}" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Nueva temporada</a>
    </div>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Año</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Default</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody>
            @foreach($seasons as $season)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $season->year }}</td>
                    <td class="px-4 py-3">{{ $season->name }}</td>
                    <td class="px-4 py-3">{{ $season->slug }}</td>
                    <td class="px-4 py-3">{{ $season->is_active ? 'Activa' : 'Inactiva' }}</td>
                    <td class="px-4 py-3">{{ $season->is_default ? 'Sí' : 'No' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('admin.seasons.edit', $season) }}" class="text-brand-700">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
    {{ $seasons->links() }}
</div>
@endsection
