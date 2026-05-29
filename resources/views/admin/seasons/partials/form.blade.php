<x-input name="year" label="Año" type="number" :value="old('year', $season->year ?? '')" required />
<x-input name="name" label="Nombre" :value="old('name', $season->name ?? '')" required />
<x-input name="slug" label="Slug" :value="old('slug', $season->slug ?? '')" required />

<div class="grid gap-3 sm:grid-cols-2">
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $season->is_active ?? true))> Activa</label>
    <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $season->is_default ?? false))> Default</label>
</div>
