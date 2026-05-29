@extends('layouts.admin')

@section('title', 'Antecedentes | Admin LBC')

@section('content')
<div class="space-y-4">
    <h1 class="text-2xl font-extrabold">Antecedentes / Inscripciones</h1>

    <x-table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Temporada</th>
                <th class="px-4 py-3">División</th>
                <th class="px-4 py-3">Club</th>
                <th class="px-4 py-3">Responsable</th>
                <th class="px-4 py-3">Envíos</th>
                <th class="px-4 py-3">Pago</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($submissions as $submission)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">{{ $submission->season->year }}</td>
                    <td class="px-4 py-3">{{ $submission->division->name }}</td>
                    <td class="px-4 py-3">{{ $submission->club->name }}</td>
                    <td class="px-4 py-3">{{ $submission->responsible_name }}</td>
                    <td class="px-4 py-3">{{ $submission->versions->count() }}/{{ $submission->max_allowed_submissions }}</td>
                    <td class="px-4 py-3">{{ $submission->payment_status }}</td>
                    <td class="px-4 py-3">{{ $submission->submission_status }}</td>
                    <td class="px-4 py-3"><a href="{{ route('admin.submissions.show', $submission) }}" class="text-brand-700">Detalle</a></td>
                </tr>
            @endforeach
        </tbody>
    </x-table>

    {{ $submissions->links() }}
</div>
@endsection
