<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name') }} - Tablero Público</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-4">
                        <a href="{{ url('/') }}" wire:navigate class="text-gray-700 hover:text-gray-900">
                            ← Inicio
                        </a>
                        <a href="{{ route('loteria') }}" wire:navigate class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Hacer reserva
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            @yield('content')
        </main>
    </body>
</html>
