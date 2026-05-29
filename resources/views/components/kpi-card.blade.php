@props(['title', 'value'])

<x-card class="bg-gradient-to-br from-white to-slate-50">
    <p class="text-xs uppercase tracking-wide text-slate-500">{{ $title }}</p>
    <p class="mt-2 text-3xl font-extrabold text-slate-900">{{ $value }}</p>
</x-card>
