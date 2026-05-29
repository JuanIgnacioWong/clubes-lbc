@props(['title', 'description'])

<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
    <p class="text-lg font-semibold text-slate-800">{{ $title }}</p>
    <p class="mt-2 text-sm text-slate-600">{{ $description }}</p>
</div>
