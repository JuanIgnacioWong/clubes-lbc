@extends('layouts.admin')

@section('title', 'Editar Temporada | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Editar temporada</h1>
    <form method="POST" action="{{ route('admin.seasons.update', $season) }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.seasons.partials.form', ['season' => $season])
        <x-button type="submit">Actualizar</x-button>
    </form>
</x-card>
@endsection
