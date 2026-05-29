@extends('layouts.admin')

@section('title', 'Nueva Temporada | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Nueva temporada</h1>
    <form method="POST" action="{{ route('admin.seasons.store') }}" class="mt-6 space-y-4">
        @csrf
        @include('admin.seasons.partials.form')
        <x-button type="submit">Guardar</x-button>
    </form>
</x-card>
@endsection
