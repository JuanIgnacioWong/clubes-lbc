<x-input name="name" label="Nombre" :value="old('name', $user->name ?? '')" required />
<x-input name="email" label="Email" type="email" :value="old('email', $user->email ?? '')" required />

<x-select name="role" label="Rol" required>
    <option value="admin" @selected(old('role', $user->role ?? 'admin') === 'admin')>admin</option>
    <option value="super_admin" @selected(old('role', $user->role ?? 'admin') === 'super_admin')>super_admin</option>
</x-select>

<x-input name="password" label="Contraseña {{ isset($user) ? '(dejar vacío para mantener)' : '' }}" type="password" {{ isset($user) ? '' : 'required' }} />

<label class="inline-flex items-center gap-2 text-sm">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
    Usuario activo
</label>
