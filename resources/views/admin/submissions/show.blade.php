@extends('layouts.admin')

@section('title', 'Detalle de Inscripción | Admin LBC')

@section('content')
<div class="space-y-6">
    <x-card>
        <h1 class="text-2xl font-extrabold">{{ $submission->club->name }}</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $submission->season->year }} · {{ $submission->division->name }}</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <form method="POST" action="{{ route('admin.submissions.payment-status', $submission) }}" class="rounded-xl border border-slate-200 p-4">
                @csrf
                @method('PATCH')
                <p class="text-sm font-semibold">Estado de pago</p>
                <select name="payment_status" class="mt-2 w-full rounded-lg border-slate-300 text-sm">
                    @foreach(['pending','in_review','paid'] as $status)
                        <option value="{{ $status }}" @selected($submission->payment_status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <x-button type="submit" class="mt-3">Actualizar pago</x-button>
            </form>

            <form method="POST" action="{{ route('admin.submissions.extra', $submission) }}" class="rounded-xl border border-slate-200 p-4">
                @csrf
                @method('PATCH')
                <p class="text-sm font-semibold">Cupo de envíos</p>
                <p class="mt-2 text-sm text-slate-600">Actual: {{ $submission->max_allowed_submissions }} (máximo 4)</p>
                <x-button type="submit" class="mt-3" variant="secondary">Habilitar envío extra</x-button>
            </form>
        </div>
    </x-card>

    <x-card>
        <h2 class="text-lg font-bold">Versiones</h2>
        <div class="mt-4 space-y-4">
            @foreach($submission->versions->sortByDesc('version_number') as $version)
                <div class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="font-semibold">Versión {{ $version->version_number }} · {{ $version->status }}</p>
                        <p class="text-xs text-slate-500">{{ optional($version->submitted_at)->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if($version->club_logo_path)
                            <a href="{{ route('admin.files.download', ['version' => $version->id, 'type' => 'logo']) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">Descargar logo</a>
                        @endif
                        @if($version->payment_receipt_path)
                            <a href="{{ route('admin.files.download', ['version' => $version->id, 'type' => 'comprobante']) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">Descargar comprobante</a>
                        @endif
                        @if($version->players_roster_path)
                            <a href="{{ route('admin.files.download', ['version' => $version->id, 'type' => 'nomina']) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs">Descargar nómina</a>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto_auto]">
                        <form method="POST" action="{{ route('admin.submissions.versions.status', ['submission' => $submission->id, 'version' => $version->id]) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="w-full rounded-lg border-slate-300 text-sm">
                                @foreach(['received','under_review','accepted','rejected','replaced'] as $status)
                                    <option value="{{ $status }}" @selected($version->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <x-button type="submit" variant="secondary">Guardar</x-button>
                        </form>

                        <form method="POST" action="{{ route('admin.submissions.versions.destroy', ['submission' => $submission->id, 'version' => $version->id]) }}" onsubmit="return confirm('¿Eliminar versión?')">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" variant="danger">Eliminar</x-button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>
@endsection
