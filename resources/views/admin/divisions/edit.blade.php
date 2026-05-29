@extends('layouts.admin')

@section('title', 'Editar División | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Editar división</h1>
    <form method="POST" action="{{ route('admin.divisions.update', $division) }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')
        @include('admin.divisions.partials.form', ['division' => $division])
        <x-button type="submit">Actualizar</x-button>
    </form>
</x-card>
@endsection
