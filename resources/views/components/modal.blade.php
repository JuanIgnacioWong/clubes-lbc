@props(['open' => false, 'title' => 'Confirmar'])

<div x-data="{ open: @js($open) }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    <div class="absolute inset-0 bg-slate-900/40" @click="open=false"></div>
    <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
        <div class="mt-2 text-sm text-slate-600">{{ $slot }}</div>
    </div>
</div>
