@extends('layouts.admin')

@section('title', 'Editar Club | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Editar club</h1>
    <form method="POST" action="{{ route('admin.clubs.update', $club) }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.clubs.partials.form', ['club' => $club])
        <x-button type="submit">Actualizar</x-button>
    </form>
</x-card>
@endsection
