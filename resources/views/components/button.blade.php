@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
$variants = [
    'primary' => 'bg-brand-700 text-white hover:bg-brand-800 focus:ring-brand-500',
    'secondary' => 'bg-slate-100 text-slate-800 hover:bg-slate-200 focus:ring-slate-400',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
    'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-400',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 '.($variants[$variant] ?? $variants['primary'])]) }}>
    {{ $slot }}
</button>
