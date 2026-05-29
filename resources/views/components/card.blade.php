@props([
    'tone' => 'default',
])

@php
    $toneClasses = match ($tone) {
        'dark' => 'border-slate-700/80 bg-slate-900 text-slate-50 shadow-2xl',
        default => 'border-slate-200 bg-white shadow-sm',
    };
@endphp

<div {{ $attributes->class("rounded-2xl border p-6 {$toneClasses}") }}>
    {{ $slot }}
</div>
