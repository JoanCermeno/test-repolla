<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Repolla Lotería</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans min-h-screen flex flex-col">
        <div class="flex-1 bg-gradient-to-br from-amber-50 via-rose-50 to-amber-100 relative overflow-hidden">
            {{-- Patrón decorativo de bolas de lotería --}}
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-20 left-10 w-24 h-24 rounded-full bg-amber-400/40 blur-2xl"></div>
                <div class="absolute bottom-32 right-20 w-32 h-32 rounded-full bg-rose-400/40 blur-2xl"></div>
                <div class="absolute top-1/2 left-1/3 w-20 h-20 rounded-full bg-yellow-400/30 blur-xl"></div>
            </div>

            <div class="relative flex flex-col items-center justify-center min-h-screen px-6 py-12">
                <h1 class="text-5xl sm:text-7xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-amber-600 via-rose-600 to-amber-700 drop-shadow-sm mb-4">
                    REPOLLA LOTERÍA
                </h1>
                <p class="text-lg text-gray-600 mb-12 max-w-md text-center">
                    Sistema de reservas por terminación jerárquica
                </p>

                <div class="flex flex-col sm:flex-row gap-4 w-full max-w-md sm:max-w-xl">
                    <a
                        href="{{ route('tablero') }}"
                        wire:navigate
                        class="flex-1 flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold text-lg
                            bg-white/90 backdrop-blur border-2 border-amber-600 text-amber-700
                            hover:bg-amber-50 hover:border-amber-700 hover:shadow-lg hover:shadow-amber-200/50
                            transition-all duration-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Ver Tablero Público
                    </a>
                    <a
                        href="{{ route('loteria') }}"
                        wire:navigate
                        class="flex-1 flex items-center justify-center gap-2 px-8 py-4 rounded-xl font-semibold text-lg
                            bg-gradient-to-r from-amber-500 to-rose-500 text-white
                            hover:from-amber-600 hover:to-rose-600
                            shadow-lg shadow-amber-500/30 hover:shadow-xl hover:shadow-amber-500/40
                            transition-all duration-200"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                        ¡Haz tu Reserva Ahora!
                    </a>
                </div>

                <p class="mt-8 text-sm text-gray-500">
                    @auth
                        <a href="{{ route('mis-reservas') }}" wire:navigate class="text-amber-600 hover:underline">Ver mis reservas</a>
                        &middot;
                        <a href="{{ route('dashboard') }}" wire:navigate class="text-amber-600 hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-amber-600 hover:underline">Iniciar sesión</a>
                        &middot;
                        <a href="{{ route('register') }}" class="text-amber-600 hover:underline">Registrarse</a>
                    @endauth
                </p>
            </div>
        </div>
    </body>
</html>
