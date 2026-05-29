@extends('layouts.public')

@section('title', 'Formulario de Inscripción | LBC Chile')

@section('content')
<div class="space-y-6" x-data="{ sending: false }">
    @if($successMessage)
        <x-alert variant="success">{{ $successMessage }}</x-alert>
    @endif

    <x-card class="bg-white/90">
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Temporada</p>
                <p class="mt-1 text-xl font-bold">{{ $season->year }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">División</p>
                <p class="mt-1 text-xl font-bold">{{ $division->name }}</p>
            </div>
            <div class="sm:text-right">
                <a href="{{ route('public.inscripciones') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">Cambiar categoría</a>
            </div>
        </div>
    </x-card>

    <x-payment-card :division="$division" />

    <x-card class="bg-white/95">
        <h2 class="text-2xl font-bold">Formulario de antecedentes</h2>
        <p class="mt-2 text-sm text-slate-600">{{ $introText }}</p>

        <form action="{{ route('public.inscripcion.store', ['season' => $season->slug, 'division' => $division->slug]) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5" @submit="sending = true">
            @csrf

            <x-select name="club_id" label="Club" required>
                <option value="">Selecciona un club</option>
                @foreach($clubs as $club)
                    <option value="{{ $club->id }}" @selected(old('club_id') == $club->id)>{{ $club->name }}</option>
                @endforeach
            </x-select>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="responsible_name" label="Nombre responsable" :value="old('responsible_name')" required />
                <x-input name="phone" label="Teléfono" :value="old('phone')" required />
            </div>

            <x-input name="email" type="email" label="Correo electrónico" :value="old('email')" required />

            @if($rosterTemplateVisible)
                <x-card class="border-sky-200 bg-sky-50">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Plantilla de nómina de jugadores</h3>
                            <p class="mt-1 text-sm text-slate-700">{{ $rosterTemplateDescription }}</p>
                        </div>
                        <a href="{{ route('public.roster-template.download') }}" class="inline-flex items-center rounded-xl border border-sky-300 bg-white px-4 py-2 text-sm font-semibold text-sky-800 hover:bg-sky-100">
                            {{ $rosterTemplateButtonText }}
                        </a>
                    </div>
                </x-card>
            @endif

            <div class="grid gap-4 sm:grid-cols-3">
                <x-file-input name="club_logo" label="Logo del club" accept=".png,.jpg,.jpeg,.webp,.svg" required />
                <x-file-input name="payment_receipt" label="Comprobante de pago" accept=".pdf,.xls,.xlsx,.docx" required />
                <x-file-input name="players_roster" label="Nómina de jugadores" accept=".pdf,.xls,.xlsx,.docx" required />
            </div>

            <x-textarea name="observations" label="Observaciones" rows="4">{{ old('observations') }}</x-textarea>

            <div class="flex items-center gap-3">
                <x-button type="submit" x-bind:disabled="sending">
                    <span x-show="!sending">Enviar inscripción</span>
                    <span x-show="sending" style="display:none;">Enviando...</span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
