@props(['label' => null, 'name'])

<label class="block space-y-2">
    @if($label)
        <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
    @endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200']) }}>
        {{ $slot }}
    </select>
    @error($name)
        <span class="text-xs text-red-600">{{ $message }}</span>
    @enderror
</label>
