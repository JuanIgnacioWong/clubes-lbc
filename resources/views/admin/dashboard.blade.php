@extends('layouts.admin')

@section('title', 'Dashboard | Admin LBC')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-extrabold">Dashboard administrativo</h1>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-kpi-card title="Total clubes" :value="$kpis['total_clubs']" />
        <x-kpi-card title="Inscripciones" :value="$kpis['inscripciones']" />
        <x-kpi-card title="Pagos pendientes" :value="$kpis['pending_payments']" />
        <x-kpi-card title="Pagos en revisión" :value="$kpis['in_review_payments']" />
        <x-kpi-card title="Pagados" :value="$kpis['paid']" />
        <x-kpi-card title="Aceptados" :value="$kpis['accepted']" />
        <x-kpi-card title="Rechazados" :value="$kpis['rejected']" />
    </div>

    <x-card>
        <h2 class="mb-4 text-lg font-bold">Últimas inscripciones</h2>
        <x-table>
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Temporada</th>
                    <th class="px-4 py-3">División</th>
                    <th class="px-4 py-3">Club</th>
                    <th class="px-4 py-3">Responsable</th>
                    <th class="px-4 py-3">Pago</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Actualizado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $row->season->year }}</td>
                        <td class="px-4 py-3">{{ $row->division->name }}</td>
                        <td class="px-4 py-3">{{ $row->club->name }}</td>
                        <td class="px-4 py-3">{{ $row->responsible_name }}</td>
                        <td class="px-4 py-3">{{ $row->payment_status }}</td>
                        <td class="px-4 py-3">{{ $row->submission_status }}</td>
                        <td class="px-4 py-3">{{ $row->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.submissions.show', $row) }}" class="text-brand-700">Ver</a></td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-slate-500" colspan="8">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">{{ $rows->links() }}</div>
    </x-card>
</div>
@endsection
