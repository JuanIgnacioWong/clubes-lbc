<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderByDesc('created_at')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active', true),
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        AuditLogger::log('user_created', 'user', $user, 'Usuario administrativo creado.', $request);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $role = $data['role'];
        $isActive = $request->boolean('is_active');

        $this->guardLastSuperAdmin($user, $role, $isActive);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'is_active' => $isActive,
        ];

        if (filled($data['password'] ?? null)) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        AuditLogger::log('user_updated', 'user', $user, 'Usuario administrativo actualizado.', $request);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            throw ValidationException::withMessages([
                'user' => 'No puedes eliminar tu propio usuario.',
            ]);
        }

        $this->guardLastSuperAdmin($user, null, false, true);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }

    private function guardLastSuperAdmin(User $target, ?string $newRole, bool $newIsActive, bool $deleting = false): void
    {
        $isSuper = $target->role === User::ROLE_SUPER_ADMIN;

        if (! $isSuper) {
            return;
        }

        $superAdmins = User::query()->where('role', User::ROLE_SUPER_ADMIN)->where('is_active', true)->count();

        $wouldRemoveSuperPowers = $deleting || $newRole !== User::ROLE_SUPER_ADMIN || ! $newIsActive;

        if ($wouldRemoveSuperPowers && $superAdmins <= 1) {
            throw ValidationException::withMessages([
                'role' => 'No se puede desactivar, degradar o eliminar el último super_admin activo.',
            ]);
        }
    }
}
