<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin LBC')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 font-body text-slate-900" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        <div class="hidden lg:block">
            <x-admin.sidebar />
        </div>

        <div x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/50" @click="sidebarOpen = false"></div>
            <div class="relative z-10 h-full w-72">
                <x-admin.sidebar />
            </div>
        </div>

        <div class="flex min-h-screen flex-1 flex-col">
            <x-admin.topbar />

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                @if(session('success'))
                    <x-alert variant="success" class="mb-6">{{ session('success') }}</x-alert>
                @endif
                @if(session('status'))
                    <x-alert variant="info" class="mb-6">{{ session('status') }}</x-alert>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
