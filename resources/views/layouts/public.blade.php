<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inscripción de Clubes | LBC Chile')</title>
    <meta name="description" content="Plataforma oficial de inscripción y recepción de antecedentes deportivos de clubes LBC Chile.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-lbc-pattern font-body text-slate-900 antialiased">
    <div class="relative min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.2),transparent_45%),radial-gradient(circle_at_bottom_left,_rgba(2,132,199,0.25),transparent_40%)]"></div>

        <header class="relative z-10 border-b border-white/50 bg-white/70 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <x-application-logo />
                    <div>
                        <p class="font-title text-lg leading-tight">{{ $platformName }}</p>
                        <p class="text-xs text-slate-600">Liga de Basquetbol Chile</p>
                    </div>
                </div>
                <a href="{{ route('public.inscripciones') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800">Inicio</a>
            </div>
        </header>

        <main class="relative z-10 mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 sm:py-12">
            @yield('content')
        </main>

        <footer class="relative z-10 border-t border-white/50 bg-white/70 py-4 text-center text-xs text-slate-600">
            © {{ date('Y') }} LBC Chile · Plataforma oficial de recepción de antecedentes.
        </footer>
    </div>
</body>
</html>
