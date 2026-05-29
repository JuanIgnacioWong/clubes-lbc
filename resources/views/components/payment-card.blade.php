@props(['division'])

@if($division->payment_is_active && filled($division->payment_url))
    <x-card class="border-brand-200 bg-brand-50/60">
        <h3 class="text-lg font-bold text-brand-900">Pago de inscripción</h3>
        @if(filled($division->payment_description))
            <p class="mt-1 text-sm text-brand-800">{{ $division->payment_description }}</p>
        @endif
        <div class="mt-4">
            <a href="{{ $division->payment_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800">
                {{ $division->payment_button_text ?: 'Pagar inscripción' }}
            </a>
        </div>
    </x-card>
@else
    <x-alert variant="warning">Esta categoría aún no tiene link de pago configurado.</x-alert>
@endif
