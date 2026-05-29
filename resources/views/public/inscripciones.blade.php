@extends('layouts.public')

@section('title', 'Inscripciones | LBC Chile')

@section('content')
<div class="grid gap-6 lg:grid-cols-[1.2fr_1fr]">
    <x-card class="relative overflow-hidden border border-slate-700/80 bg-slate-900 p-8 text-slate-50 shadow-2xl">
        <div class="absolute -right-10 -top-10 h-44 w-44 rounded-full bg-brand-500/30 blur-2xl"></div>
        <div class="absolute -bottom-12 -left-8 h-44 w-44 rounded-full bg-sky-400/20 blur-2xl"></div>

        <div class="relative">
            <p class="text-xs uppercase tracking-[0.22em] text-sky-200">Plataforma oficial</p>
            <h1 class="mt-3 text-3xl font-extrabold sm:text-4xl">Inscripción de clubes</h1>
            @if($activeSeason)
                <p class="mt-6 inline-flex rounded-full border border-slate-500 bg-slate-800/80 px-4 py-1.5 text-sm font-semibold text-slate-50">
                    Temporada {{ $activeSeason->year }}
                </p>

                <form action="{{ route('public.inscripciones.continuar') }}" method="POST" class="mt-6 space-y-4" x-data="{ division: '' }">
                    @csrf
                    <input type="hidden" name="season" value="{{ $activeSeason->slug }}">

                    <label class="block space-y-2">
                        <span class="text-sm font-semibold text-slate-50">División / Categoría</span>
                        <select name="division" x-model="division" required class="w-full rounded-xl border border-slate-500 bg-slate-950 px-4 py-3 text-sm text-white focus:border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-400/40">
                            <option value="" disabled>Selecciona una categoría</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->slug }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    @error('division')
                        <x-alert variant="danger">{{ $message }}</x-alert>
                    @enderror

                    <x-button type="submit" x-bind:disabled="!division" class="w-full sm:w-auto">
                        Continuar inscripción
                    </x-button>
                </form>
            @else
                <div class="mt-6">
                    <x-alert variant="warning">No existe una temporada activa configurada. Contacta al administrador.</x-alert>
                </div>
            @endif

            @if($activeSeason && $divisions->isEmpty())
                <div class="mt-6">
                    <x-alert variant="warning">No hay divisiones activas disponibles para la temporada seleccionada.</x-alert>
                </div>
            @endif
        </div>
    </x-card>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
        @foreach([
            ['01', 'Selecciona tu categoría', 'Elige la división correspondiente para filtrar el formulario.'],
            ['02', 'Elige tu club', 'Selecciona tu club oficial dentro del listado activo.'],
            ['03', 'Sube antecedentes', 'Adjunta logo, comprobante y nómina en formatos válidos.'],
            ['04', 'Envía inscripción', 'Se guardará historial de versiones para revisión administrativa.'],
        ] as [$step, $title, $description])
            <x-card class="border-slate-200/80 bg-white/90">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700">Paso {{ $step }}</p>
                <h3 class="mt-2 text-lg font-bold">{{ $title }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
            </x-card>
        @endforeach
    </div>
</div>
@endsection
