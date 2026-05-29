<x-select name="season_id" label="Temporada" required>
    @foreach($seasons as $season)
        <option value="{{ $season->id }}" @selected(old('season_id', $club->season_id ?? '') == $season->id)>{{ $season->year }} - {{ $season->name }}</option>
    @endforeach
</x-select>

<x-select name="division_id" label="División" required>
    @foreach($divisions as $division)
        <option value="{{ $division->id }}" @selected(old('division_id', $club->division_id ?? '') == $division->id)>{{ $division->name }} ({{ $division->season->year }})</option>
    @endforeach
</x-select>

<x-input name="name" label="Nombre" :value="old('name', $club->name ?? '')" required />
<x-input name="slug" label="Slug" :value="old('slug', $club->slug ?? '')" required />
<x-input name="contact_name" label="Contacto" :value="old('contact_name', $club->contact_name ?? '')" />
<x-input name="contact_email" label="Email contacto" :value="old('contact_email', $club->contact_email ?? '')" />
<x-input name="contact_phone" label="Teléfono contacto" :value="old('contact_phone', $club->contact_phone ?? '')" />
<x-input name="sort_order" label="Orden" type="number" :value="old('sort_order', $club->sort_order ?? 0)" />
<label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $club->is_active ?? true))> Club activo</label>
