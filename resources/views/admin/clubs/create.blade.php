@extends('layouts.admin')

@section('title', 'Nuevo Club | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Nuevo club</h1>
    <form method="POST" action="{{ route('admin.clubs.store') }}" class="mt-6 space-y-4">
        @csrf
        @include('admin.clubs.partials.form')
        <x-button type="submit">Guardar</x-button>
    </form>
</x-card>
@endsection
