<x-select name="season_id" label="Temporada" required>
    @foreach($seasons as $season)
        <option value="{{ $season->id }}" @selected(old('season_id', $division->season_id ?? '') == $season->id)>{{ $season->year }} - {{ $season->name }}</option>
    @endforeach
</x-select>

<x-input name="name" label="Nombre" :value="old('name', $division->name ?? '')" required />
<x-input name="slug" label="Slug" :value="old('slug', $division->slug ?? '')" required />
<x-textarea name="description" label="Descripción" rows="3">{{ old('description', $division->description ?? '') }}</x-textarea>
<x-input name="sort_order" label="Orden" type="number" :value="old('sort_order', $division->sort_order ?? 0)" />

<div class="grid gap-3 sm:grid-cols-2">
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $division->is_active ?? true))> División activa</label>
</div>

<hr class="my-4">
<h3 class="text-lg font-bold">Configuración de pago</h3>
<x-input name="payment_url" label="URL de pago" :value="old('payment_url', $division->payment_url ?? '')" />
<x-input name="payment_button_text" label="Texto botón" :value="old('payment_button_text', $division->payment_button_text ?? '')" placeholder="Pagar inscripción" />
<x-input name="payment_description" label="Descripción pago" :value="old('payment_description', $division->payment_description ?? '')" />
<label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="payment_is_active" value="1" @checked(old('payment_is_active', $division->payment_is_active ?? false))> Link de pago activo</label>
