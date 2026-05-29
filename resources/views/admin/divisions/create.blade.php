@extends('layouts.admin')

@section('title', 'Nueva División | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Nueva división</h1>
    <form method="POST" action="{{ route('admin.divisions.store') }}" class="mt-6 space-y-4">
        @csrf
        @include('admin.divisions.partials.form')
        <x-button type="submit">Guardar</x-button>
    </form>
</x-card>
@endsection
