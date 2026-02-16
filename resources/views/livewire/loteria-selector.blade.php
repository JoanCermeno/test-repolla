<?php

use App\Models\Reserva;
use App\Services\TerminacionService;
use Livewire\Volt\Component;

new class extends Component
{
    public array $carrito = [];

    public ?string $mensajeError = null;

    public string $mensajeExito = '';

    public string $searchNivel3 = '';

    public string $searchNivel4 = '';

    public int $pageNivel3 = 1;

    public int $pageNivel4 = 1;

    public int $perPage = 100;

    private function terminacion(): TerminacionService
    {
        return app(TerminacionService::class);
    }

    private function comprasCompletas(): array
    {
        $deDb = Reserva::comprasExistentes();
        return array_merge($deDb, $this->carrito);
    }

    public function agregarAlCarrito(int $numero, int $nivel): void
    {
        $this->mensajeError = null;
        $this->mensajeExito = '';

        $compras = $this->comprasCompletas();
        if (! $this->terminacion()->esSeleccionable($numero, $nivel, $compras)) {
            $this->mensajeError = 'Este número no está disponible (bloqueado por jerarquía).';
            return;
        }

        $this->carrito[] = ['numero' => $numero, 'nivel' => $nivel];
    }

    public function quitarDelCarrito(int $index): void
    {
        array_splice($this->carrito, $index, 1);
    }

    public function confirmarReserva(): void
    {
        if (empty($this->carrito)) {
            $this->mensajeError = 'Agrega al menos un número al carrito.';
            return;
        }

        $nombreCliente = auth()->user()->name;

        foreach ($this->carrito as $item) {
            Reserva::create([
                'user_id' => auth()->id(),
                'numero' => $item['numero'],
                'nivel' => $item['nivel'],
                'nombre_cliente' => $nombreCliente,
                'telefono_cliente' => null,
            ]);
        }

        $this->carrito = [];
        $this->mensajeError = null;
        $this->mensajeExito = '¡Reserva confirmada correctamente!';
    }

    public function estadoDe(int $numero, int $nivel): string
    {
        $compras = $this->comprasCompletas();
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

<div class="py-8 relative" wire:loading.class="opacity-70 pointer-events-none">
    <div wire:loading class="absolute top-4 right-4 px-3 py-1 bg-indigo-600 text-white text-sm rounded-full">
        Cargando...
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-6">
        {{-- Contenido principal --}}
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Selector de Lotería</h1>

            @if ($mensajeError)
                <div class="mb-4 p-3 bg-amber-100 text-amber-800 rounded">{{ $mensajeError }}</div>
            @endif
            @if ($mensajeExito)
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ $mensajeExito }}</div>
            @endif

            {{-- Nivel 1: Unidades (0-9) --}}
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 1 - Unidades (0-9)</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach (range(0, 9) as $n)
                        @php $estado = $this->estadoDe($n, 1); @endphp
                        <button
                            type="button"
                            wire:click="agregarAlCarrito({{ $n }}, 1)"
                            wire:loading.attr="disabled"
                            @disabled($estado === 'bloqueado')
                            @class([
                                'px-4 py-2 rounded font-medium transition',
                                'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                'bg-blue-500 text-white' => $estado === 'adquirido',
                                'bg-gray-300 text-gray-500 cursor-not-allowed' => $estado === 'bloqueado',
                            ])
                        >
                            {{ $n }}
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- Nivel 2: Decenas (00-99) --}}
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 2 - Decenas (00-99)</h2>
                <div class="flex flex-wrap gap-1">
                    @foreach (range(0, 99) as $n)
                        @php $estado = $this->estadoDe($n, 2); @endphp
                        <button
                            type="button"
                            wire:click="agregarAlCarrito({{ $n }}, 2)"
                            wire:loading.attr="disabled"
                            @disabled($estado === 'bloqueado')
                            @class([
                                'px-2 py-1 text-sm rounded font-medium transition min-w-[2.5rem]',
                                'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                'bg-blue-500 text-white' => $estado === 'adquirido',
                                'bg-gray-300 text-gray-500 cursor-not-allowed' => $estado === 'bloqueado',
                            ])
                        >
                            {{ str_pad((string) $n, 2, '0', STR_PAD_LEFT) }}
                        </button>
                    @endforeach
                </div>
            </section>

            {{-- Nivel 3: Centenas (000-999) con búsqueda y paginación --}}
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 3 - Centenas (000-999)</h2>
                <div class="mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchNivel3"
                        placeholder="Buscar (ej: 12 para números que contengan 12)"
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
                        <button
                            type="button"
                            wire:click="agregarAlCarrito({{ $n }}, 3)"
                            wire:loading.attr="disabled"
                            @disabled($estado === 'bloqueado')
                            @class([
                                'px-2 py-1 text-xs rounded font-medium transition min-w-[2.75rem]',
                                'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                'bg-blue-500 text-white' => $estado === 'adquirido',
                                'bg-gray-300 text-gray-500 cursor-not-allowed' => $estado === 'bloqueado',
                            ])
                        >
                            {{ str_pad((string) $n, 3, '0', STR_PAD_LEFT) }}
                        </button>
                    @endforeach
                </div>
                @if (count($todos3) > $perPage)
                    <div class="mt-3 flex gap-2 items-center">
                        <button
                            wire:click="anteriorNivel3"
                            @disabled($pageNivel3 <= 1)
                            class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50"
                        >
                            Anterior
                        </button>
                        <span class="text-sm text-gray-600">
                            Página {{ $pageNivel3 }} de {{ $totalPages3 }}
                        </span>
                        <button
                            wire:click="siguienteNivel3"
                            @disabled($pageNivel3 >= $totalPages3)
                            class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50"
                        >
                            Siguiente
                        </button>
                    </div>
                @endif
            </section>

            {{-- Nivel 4: Unidades de mil (0000-9999) con búsqueda y paginación --}}
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Nivel 4 - Milésimas (0000-9999)</h2>
                <div class="mb-3">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchNivel4"
                        placeholder="Buscar (ej: 12 para números que contengan 12)"
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
                        <button
                            type="button"
                            wire:click="agregarAlCarrito({{ $n }}, 4)"
                            wire:loading.attr="disabled"
                            @disabled($estado === 'bloqueado')
                            @class([
                                'px-2 py-1 text-xs rounded font-medium transition min-w-[2.75rem]',
                                'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                'bg-blue-500 text-white' => $estado === 'adquirido',
                                'bg-gray-300 text-gray-500 cursor-not-allowed' => $estado === 'bloqueado',
                            ])
                        >
                            {{ str_pad((string) $n, 4, '0', STR_PAD_LEFT) }}
                        </button>
                    @endforeach
                </div>
                @if (count($todos4) > $perPage)
                    <div class="mt-3 flex gap-2 items-center">
                        <button
                            wire:click="anteriorNivel4"
                            @disabled($pageNivel4 <= 1)
                            class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50"
                        >
                            Anterior
                        </button>
                        <span class="text-sm text-gray-600">
                            Página {{ $pageNivel4 }} de {{ $totalPages4 }}
                        </span>
                        <button
                            wire:click="siguienteNivel4"
                            @disabled($pageNivel4 >= $totalPages4)
                            class="px-3 py-1 rounded bg-gray-200 disabled:opacity-50"
                        >
                            Siguiente
                        </button>
                    </div>
                @endif
            </section>

            <div class="mt-6 flex gap-4 text-sm">
                <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-green-500"></span> Disponible</span>
                <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-blue-500"></span> Tu selección / Reservado</span>
                <span class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-gray-300"></span> Bloqueado</span>
            </div>
        </div>

        {{-- Sidebar: Carrito de Compras --}}
        <aside class="w-80 shrink-0">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 sticky top-4">
                <h3 class="font-semibold text-gray-900 mb-3">Carrito de Compras</h3>
                <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                    @forelse ($carrito as $index => $item)
                        <div class="flex justify-between items-center text-sm py-1 border-b border-gray-100">
                            <span class="font-mono">
                                {{ str_pad((string) $item['numero'], $item['nivel'], '0', STR_PAD_LEFT) }}
                                <span class="text-gray-500">(N{{ $item['nivel'] }})</span>
                            </span>
                            <button
                                type="button"
                                wire:click="quitarDelCarrito({{ $index }})"
                                class="text-red-600 hover:text-red-800 text-xs"
                            >
                                Quitar
                            </button>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Vacío. Haz clic en un número disponible.</p>
                    @endforelse
                </div>
                @if (count($carrito) > 0)
                    <form wire:submit="confirmarReserva">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50 font-medium"
                        >
                            <span wire:loading.remove>Confirmar Reserva</span>
                            <span wire:loading>Guardando...</span>
                        </button>
                    </form>
                @endif
            </div>
        </aside>
    </div>
</div>
