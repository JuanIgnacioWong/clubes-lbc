@extends('layouts.admin')

@section('title', 'Crear Usuario | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Nuevo usuario</h1>

    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 space-y-4">
        @csrf
        @include('admin.users.partials.form')
        <x-button type="submit">Guardar usuario</x-button>
    </form>
</x-card>
@endsection
