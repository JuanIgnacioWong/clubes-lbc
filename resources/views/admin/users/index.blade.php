@extends('layouts.admin')

@section('title', 'Usuarios | Admin LBC')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-extrabold">Usuarios administrativos</h1>
        <a href="{{ route('admin.users.create') }}" class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Nuevo usuario</a>
    </div>

    @if($errors->has('role') || $errors->has('user'))
        <x-alert variant="danger">{{ $errors->first('role') ?: $errors->first('user') }}</x-alert>
    @endif

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Nombre</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Rol</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Creado</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->role }}</td>
                    <td class="px-4 py-3">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</td>
                    <td class="px-4 py-3">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">Editar</a>
                            @if((int) auth()->id() !== (int) $user->id)
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('¿Eliminar usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-300 px-3 py-1.5 text-xs text-red-700">Eliminar</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-table>

    {{ $users->links() }}
</div>
@endsection
