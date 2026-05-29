<aside class="flex h-full w-72 flex-col border-r border-slate-200 bg-slate-950 text-slate-100">
    <div class="border-b border-slate-800 px-6 py-5">
        <x-application-logo class="h-10 w-auto max-w-[180px]" />
        <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $platformName }}</p>
        <p class="mt-1 text-lg font-bold">Panel Administrativo</p>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
        <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800' : '' }}">Dashboard</a>
        <a href="{{ route('admin.seasons.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.seasons.*') ? 'bg-slate-800' : '' }}">Temporadas</a>
        <a href="{{ route('admin.divisions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.divisions.*') ? 'bg-slate-800' : '' }}">Divisiones</a>
        <a href="{{ route('admin.clubs.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.clubs.*') ? 'bg-slate-800' : '' }}">Clubes</a>
        <a href="{{ route('admin.submissions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.submissions.*') ? 'bg-slate-800' : '' }}">Antecedentes</a>
        <a href="{{ route('admin.corrections.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.corrections.*') ? 'bg-slate-800' : '' }}">Correcciones</a>
        <a href="{{ route('admin.pagos.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.pagos.*') ? 'bg-slate-800' : '' }}">Pagos</a>
        <a href="{{ route('admin.historial.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.historial.*') ? 'bg-slate-800' : '' }}">Historial</a>
        <a href="{{ route('admin.configuracion.edit') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.configuracion.*') ? 'bg-slate-800' : '' }}">Configuración</a>
        <a href="{{ route('admin.users.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800 {{ request()->routeIs('admin.users.*') ? 'bg-slate-800' : '' }}">Usuarios</a>
    </nav>
</aside>
