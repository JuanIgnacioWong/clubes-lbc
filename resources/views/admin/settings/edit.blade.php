@extends('layouts.admin')

@section('title', 'Configuración | Admin LBC')

@section('content')
<x-card>
    <h1 class="text-2xl font-extrabold">Configuración global</h1>
    <p class="mt-1 text-sm text-slate-600">Ajusta textos, branding, límites y datos base de la plataforma.</p>

    <form method="POST" action="{{ route('admin.configuracion.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <h2 class="text-xl font-bold">Identidad institucional</h2>
            <x-input name="platform_name" label="Nombre de plataforma" :value="old('platform_name', $values['platform_name'])" required />

            <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <x-file-input name="platform_logo" label="Logo institucional" accept=".png,.jpg,.jpeg,.webp,.svg" />
                <p class="text-sm text-slate-600">Subir logo en formato rectangular/horizontal para mejor visualización. Formatos: PNG, JPG, JPEG, WEBP, SVG. Máximo 2MB.</p>

                @if($platformLogoUrl)
                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Preview actual</p>
                        <img
                            src="{{ $platformLogoUrl }}"
                            alt="{{ old('platform_name', $values['platform_name']) }}"
                            class="h-10 w-auto max-w-[140px] object-contain sm:max-w-[180px]"
                        >
                    </div>
                @endif

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="remove_platform_logo" value="1" @checked(old('remove_platform_logo'))>
                    Eliminar logo institucional actual
                </label>
            </div>

            <x-textarea name="inscripciones_intro" label="Texto introductorio /inscripciones" rows="3">{{ old('inscripciones_intro', $values['inscripciones_intro']) }}</x-textarea>
            <x-textarea name="inscripcion_intro" label="Texto introductorio formulario" rows="3">{{ old('inscripcion_intro', $values['inscripcion_intro']) }}</x-textarea>
            <x-textarea name="inscripcion_success_message" label="Mensaje de éxito" rows="2">{{ old('inscripcion_success_message', $values['inscripcion_success_message']) }}</x-textarea>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="notification_email" label="Correo de notificaciones" :value="old('notification_email', $values['notification_email'])" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="max_logo_mb" label="Máx logo (MB)" type="number" :value="old('max_logo_mb', $values['max_logo_mb'])" required />
                <x-input name="max_documents_mb" label="Máx documentos (MB)" type="number" :value="old('max_documents_mb', $values['max_documents_mb'])" required />
            </div>

            <x-input name="allowed_formats" label="Formatos permitidos" :value="old('allowed_formats', $values['allowed_formats'])" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="brand_primary_color" label="Color principal (#RRGGBB)" :value="old('brand_primary_color', $values['brand_primary_color'])" />
                <x-input name="brand_secondary_color" label="Color secundario (#RRGGBB)" :value="old('brand_secondary_color', $values['brand_secondary_color'])" />
            </div>
        </div>

        <hr>

        <div class="space-y-4">
            <h2 class="text-xl font-bold">Plantilla de nómina de jugadores</h2>
            <p class="text-sm text-slate-600">Sube una plantilla global (misma para todas las divisiones). Formatos permitidos: PDF, DOC, DOCX, XLS, XLSX. Máximo 10MB.</p>

            <x-file-input name="roster_template_file" label="Subir/Reemplazar plantilla" accept=".pdf,.doc,.docx,.xls,.xlsx" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="roster_template_button_text" label="Texto del botón" :value="old('roster_template_button_text', $rosterTemplate['roster_template_button_text'])" />
                <x-input name="roster_template_description" label="Descripción breve" :value="old('roster_template_description', $rosterTemplate['roster_template_description'])" />
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="roster_template_is_active" value="1" @checked(old('roster_template_is_active', $rosterTemplate['roster_template_is_active'] === '1'))>
                    Plantilla activa
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_roster_template" value="1">
                    Eliminar archivo actual
                </label>
            </div>

            <x-card class="bg-slate-50">
                <div class="grid gap-2 text-sm sm:grid-cols-2">
                    <p><span class="font-semibold">Archivo original:</span> {{ $rosterTemplate['roster_template_original_name'] ?: 'No cargado' }}</p>
                    <p><span class="font-semibold">Extensión:</span> {{ $rosterTemplate['roster_template_extension'] ?: '-' }}</p>
                    <p><span class="font-semibold">MIME:</span> {{ $rosterTemplate['roster_template_mime'] ?: '-' }}</p>
                    <p><span class="font-semibold">Última actualización:</span> {{ $rosterTemplate['roster_template_updated_at'] ?: '-' }}</p>
                    <p><span class="font-semibold">Estado:</span> {{ $rosterTemplate['roster_template_is_active'] === '1' ? 'Activo' : 'Inactivo' }}</p>
                    <p><span class="font-semibold">Disponible físicamente:</span> {{ $rosterTemplateAvailable ? 'Sí' : 'No' }}</p>
                </div>

                @if($rosterTemplateAvailable)
                    <div class="mt-4">
                        <a href="{{ route('admin.configuracion.roster-template.download') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold hover:bg-white">
                            Descargar plantilla actual ({{ $rosterTemplateDownloadName }})
                        </a>
                    </div>
                @endif
            </x-card>
        </div>

        <x-button type="submit">Guardar configuración</x-button>
    </form>
</x-card>
@endsection
