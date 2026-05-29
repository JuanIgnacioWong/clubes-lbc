@extends('layouts.admin')

@section('title', 'Divisiones | Admin LBC')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Divisiones / Categorías</h1>
        <a href="{{ route('admin.divisions.create') }}" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Nueva división</a>
    </div>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr><th class="px-4 py-3">Temporada</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Slug</th><th class="px-4 py-3">Pago</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody>
            @foreach($divisions as $division)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $division->season->year }}</td>
                    <td class="px-4 py-3">{{ $division->name }}</td>
                    <td class="px-4 py-3">{{ $division->slug }}</td>
                    <td class="px-4 py-3">{{ $division->payment_is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td class="px-4 py-3">{{ $division->is_active ? 'Activa' : 'Inactiva' }}</td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('admin.divisions.edit', $division) }}" class="text-brand-700">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>
    {{ $divisions->links() }}
</div>
@endsection
