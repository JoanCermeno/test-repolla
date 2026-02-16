<?php

use App\Models\Reserva;
use App\Services\TerminacionService;
use Livewire\Volt\Component;

new class extends Component
{
    public string $searchNivel3 = '';

    public string $searchNivel4 = '';

    public int $pageNivel3 = 1;

    public int $pageNivel4 = 1;

    public int $perPage = 100;

    private function terminacion(): TerminacionService
    {
        return app(TerminacionService::class);
    }

    private function compras(): array
    {
        return Reserva::comprasExistentes();
    }

    public function estadoDe(int $numero, int $nivel): string
    {
        $compras = $this->compras();
        $estaComprado = collect($compras)->contains(fn ($c) => $c['numero'] === $numero && $c['nivel'] === $nivel);
        if ($estaComprado) {
            return 'adquirido';
        }
        if (! $this->terminacion()->esSeleccionable($numero, $nivel, $compras)) {
            return 'bloqueado';
        }
        return 'disponible';
    }

    public function numerosNivel3(): array
    {
        $numeros = range(0, 999);
        if ($this->searchNivel3 !== '') {
            $busqueda = $this->searchNivel3;
            $numeros = array_filter($numeros, fn ($n) => str_contains(str_pad((string) $n, 3, '0', STR_PAD_LEFT), $busqueda));
        }
        return array_values($numeros);
    }

    public function numerosNivel4(): array
    {
        $numeros = range(0, 9999);
        if ($this->searchNivel4 !== '') {
            $busqueda = $this->searchNivel4;
            $numeros = array_filter($numeros, fn ($n) => str_contains(str_pad((string) $n, 4, '0', STR_PAD_LEFT), $busqueda));
        }
        return array_values($numeros);
    }

    public function anteriorNivel3(): void
    {
        $this->pageNivel3 = max(1, $this->pageNivel3 - 1);
    }

    public function siguienteNivel3(): void
    {
        $todos = $this->numerosNivel3();
        $totalPages = (int) max(1, ceil(count($todos) / $this->perPage));
        $this->pageNivel3 = min($totalPages, $this->pageNivel3 + 1);
    }

    public function anteriorNivel4(): void
    {
        $this->pageNivel4 = max(1, $this->pageNivel4 - 1);
    }

    public function siguienteNivel4(): void
    {
        $todos = $this->numerosNivel4();
        $totalPages = (int) max(1, ceil(count($todos) / $this->perPage));
        $this->pageNivel4 = min($totalPages, $this->pageNivel4 + 1);
    }
}; ?>

<div class="py-8" wire:loading.class="opacity-70">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Tablero Público</h1>
        <p class="text-gray-600 mb-6">Vista de solo lectura de los números reservados.</p>

        {{-- Nivel 1 --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 1 - Unidades (0-9)</h2>
            <div class="flex flex-wrap gap-2">
                @foreach (range(0, 9) as $n)
                    @php $estado = $this->estadoDe($n, 1); @endphp
                    <span
                        @class([
                            'px-4 py-2 rounded font-medium inline-block',
                            'bg-green-500 text-white' => $estado === 'disponible',
                            'bg-blue-500 text-white' => $estado === 'adquirido',
                            'bg-gray-300 text-gray-500' => $estado === 'bloqueado',
                        ])
                    >
                        {{ $n }}
                    </span>
                @endforeach
            </div>
        </section>

        {{-- Nivel 2 --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 2 - Decenas (00-99)</h2>
            <div class="flex flex-wrap gap-1">
                @foreach (range(0, 99) as $n)
                    @php $estado = $this->estadoDe($n, 2); @endphp
                    <span
                        @class([
                            'px-2 py-1 text-sm rounded font-medium inline-block min-w-[2.5rem] text-center',
                            'bg-green-500 text-white' => $estado === 'disponible',
                            'bg-blue-500 text-white' => $estado === 'adquirido',
                            'bg-gray-300 text-gray-500' => $estado === 'bloqueado',
                        ])
                    >
                        {{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}
                    </span>
                @endforeach
            </div>
        </section>

        {{-- Nivel 3 con búsqueda y paginación --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 3 - Centenas (000-999)</h2>
            <div class="mb-3">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchNivel3"
                    placeholder="Buscar (ej: 12)"
                    class="rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full max-w-xs"
                />
            </div>
            @php
                $todos3 = $this->numerosNivel3();
                $paginated3 = collect($todos3)->forPage($pageNivel3, $perPage)->values()->all();
                $totalPages3 = (int) max(1, ceil(count($todos3) / $perPage));
            @endphp
            <div class="flex flex-wrap gap-1">
                @foreach ($paginated3 as $n)
                    @php $estado = $this->estadoDe($n, 3); @endphp
                    <span
                        @class([
                            'px-2 py-1 text-xs rounded font-medium inline-block min-w-[2.75rem] text-center',
                            'bg-green-500 text-white' => $estado === 'disponible',
                            'bg-blue-500 text-white' => $estado === 'adquirido',
                            'bg-gray-300 text-gray-500' => $estado === 'bloqueado',
                        ])
                    >
                        {{ str_pad((string) $n, 3, '0', STR_PAD_LEFT) }}
                    </span>
                @endforeach
            </div>
            @if (count($todos3) > $perPage)
                <div class="mt-3 flex gap-2 items-center">
                    <button wire:click="anteriorNivel3" @disabled($pageNivel3 <= 1) class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50">
                        Anterior
                    </button>
                    <span class="text-sm text-gray-600">Página {{ $pageNivel3 }} de {{ $totalPages3 }}</span>
                    <button wire:click="siguienteNivel3" @disabled($pageNivel3 >= $totalPages3) class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50">
                        Siguiente
                    </button>
                </div>
            @endif
        </section>

        {{-- Nivel 4 con búsqueda y paginación --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 4 - Milésimas (0000-9999)</h2>
            <div class="mb-3">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="searchNivel4"
                    placeholder="Buscar (ej: 12)"
                    class="rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 w-full max-w-xs"
                />
            </div>
            @php
                $todos4 = $this->numerosNivel4();
                $paginated4 = collect($todos4)->forPage($pageNivel4, $perPage)->values()->all();
                $totalPages4 = (int) max(1, ceil(count($todos4) / $perPage));
            @endphp
            <div class="flex flex-wrap gap-1">
                @foreach ($paginated4 as $n)
                    @php $estado = $this->estadoDe($n, 4); @endphp
                    <span
                        @class([
                            'px-2 py-1 text-xs rounded font-medium inline-block min-w-[2.75rem] text-center',
                            'bg-green-500 text-white' => $estado === 'disponible',
                            'bg-blue-500 text-white' => $estado === 'adquirido',
                            'bg-gray-300 text-gray-500' => $estado === 'bloqueado',
                        ])
                    >
                        {{ str_pad((string) $n, 4, '0', STR_PAD_LEFT) }}
                    </span>
                @endforeach
            </div>
            @if (count($todos4) > $perPage)
                <div class="mt-3 flex gap-2 items-center">
                    <button wire:click="anteriorNivel4" @disabled($pageNivel4 <= 1) class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50">
                        Anterior
                    </button>
                    <span class="text-sm text-gray-600">Página {{ $pageNivel4 }} de {{ $totalPages4 }}</span>
                    <button wire:click="siguienteNivel4" @disabled($pageNivel4 >= $totalPages4) class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50">
                        Siguiente
                    </button>
                </div>
            @endif
        </section>

        <div class="mt-6 flex gap-4 text-sm">
            <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-green-500"></span> Disponible</span>
            <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-blue-500"></span> Reservado</span>
            <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-gray-300"></span> Bloqueado</span>
        </div>
    </div>
</div>
