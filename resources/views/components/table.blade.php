<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
    <table {{ $attributes->merge(['class' => 'min-w-full text-sm']) }}>
        {{ $slot }}
    </table>
</div>
