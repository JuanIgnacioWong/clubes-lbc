<header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm lg:hidden" @click="sidebarOpen = true">Menú</button>
            <x-application-logo class="h-10 w-auto max-w-[140px] shrink-0 sm:max-w-[180px] lg:hidden" />
        </div>
        <div class="hidden text-sm text-slate-600 md:block">{{ now()->format('d/m/Y H:i') }}</div>
        <div class="flex items-center gap-3 text-sm">
            <span class="font-semibold text-slate-700">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-white">Salir</button>
            </form>
        </div>
    </div>
</header>
