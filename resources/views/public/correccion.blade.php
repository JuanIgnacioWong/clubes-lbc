@extends('layouts.public')

@section('title', 'Corrección de Antecedentes | LBC Chile')

@section('content')
<div class="space-y-6" x-data="{ sending: false }">
    @if(session('success'))
        <x-alert variant="success">{{ session('success') }}</x-alert>
    @endif

    <x-alert variant="info">Estás ingresando mediante un enlace de corrección controlado por administración.</x-alert>

    <x-card class="bg-white/95">
        <div class="grid gap-4 sm:grid-cols-3">
            <div><p class="text-xs text-slate-500">Temporada</p><p class="text-xl font-bold">{{ $season->year }}</p></div>
            <div><p class="text-xs text-slate-500">División</p><p class="text-xl font-bold">{{ $division->name }}</p></div>
            <div><p class="text-xs text-slate-500">Club</p><p class="text-xl font-bold">{{ $club->name }}</p></div>
        </div>

        <form action="{{ route('public.correcciones.store', ['year' => $season->slug, 'division' => $division->slug, 'club' => $club->slug, 'token' => $link->token]) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5" @submit="sending = true">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="responsible_name" label="Nombre responsable" :value="old('responsible_name')" required />
                <x-input name="phone" label="Teléfono" :value="old('phone')" required />
            </div>

            <x-input name="email" type="email" label="Correo electrónico" :value="old('email')" required />

            <div class="grid gap-4 sm:grid-cols-3">
                <x-file-input name="club_logo" label="Logo corregido" accept=".png,.jpg,.jpeg,.webp,.svg" />
                <x-file-input name="payment_receipt" label="Comprobante corregido" accept=".pdf,.xls,.xlsx,.docx" />
                <x-file-input name="players_roster" label="Nómina corregida" accept=".pdf,.xls,.xlsx,.docx" />
            </div>

            <x-textarea name="observations" label="Observaciones" rows="4">{{ old('observations') }}</x-textarea>

            <x-button type="submit" x-bind:disabled="sending">
                <span x-show="!sending">Enviar corrección</span>
                <span x-show="sending" style="display:none;">Enviando...</span>
            </x-button>
        </form>
    </x-card>
</div>
@endsection
