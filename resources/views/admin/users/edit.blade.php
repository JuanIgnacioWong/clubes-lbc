@extends('layouts.admin')

@section('title', 'Editar Usuario | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Editar usuario</h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.users.partials.form', ['user' => $user])
        <x-button type="submit">Actualizar usuario</x-button>
    </form>
</x-card>
@endsection
