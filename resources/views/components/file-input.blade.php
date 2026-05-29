@props(['label' => null, 'name'])

<label class="block space-y-2" x-data="{ fileName: '' }">
    @if($label)
        <span class="text-sm font-semibold text-slate-700">{{ $label }}</span>
    @endif
    <input type="file" name="{{ $name }}" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" {{ $attributes->merge(['class' => 'w-full rounded-xl border border-dashed border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200']) }}>
    <span class="text-xs text-slate-500" x-text="fileName || 'Ningún archivo seleccionado'"></span>
    @error($name)
        <span class="text-xs text-red-600">{{ $message }}</span>
    @enderror
</label>
