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
    <div class="py-8 relative min-h-screen bg-gray-50" wire:loading.class="opacity-70 pointer-events-none">
        <div wire:loading class="fixed top-4 right-4 z-50 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-full shadow-lg">
            Cargando sistema...
        </div>
    
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Título Principal --}}
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">REPOLLA <span class="text-indigo-600">LOTERÍA</span></h1>
                <p class="text-gray-500 mt-2">Selecciona tus números por terminación</p>
            </div>
    
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Contenido principal: Tableros --}}
                <div class="flex-1 space-y-10">
                    
                    @if ($mensajeError)
                        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-sm">{{ $mensajeError }}</div>
                    @endif
                    @if ($mensajeExito)
                        <div class="p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm">{{ $mensajeExito }}</div>
                    @endif
    
                    {{-- Niveles 1, 2 y 3: DISEÑO DE CUADROS GRANDES --}}
                    @foreach([
                        ['titulo' => 'Nivel 1 - Unidades', 'rango' => range(0, 9), 'nivel' => 1, 'pad' => 1],
                        ['titulo' => 'Nivel 2 - Decenas', 'rango' => range(0, 99), 'nivel' => 2, 'pad' => 2],
                    ] as $seccion)
                        <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                                <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                                {{ $seccion['titulo'] }}
                            </h2>
                            <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 gap-3">
                                @foreach ($seccion['rango'] as $n)
                                    @php $estado = $this->estadoDe($n, $seccion['nivel']); @endphp
                                    <button
                                        type="button"
                                        wire:click="agregarAlCarrito({{ $n }}, {{ $seccion['nivel'] }})"
                                        @disabled($estado === 'bloqueado')
                                        @class([
                                            'h-14 sm:h-16 flex items-center justify-center rounded-xl font-bold text-lg transition-all transform active:scale-95 shadow-sm',
                                            'bg-green-500 text-white hover:bg-green-600 hover:shadow-md' => $estado === 'disponible',
                                            'bg-blue-600 text-white ring-4 ring-blue-100' => $estado === 'adquirido',
                                            'bg-gray-100 text-gray-300 cursor-not-allowed border border-gray-200' => $estado === 'bloqueado',
                                        ])
                                    >
                                        {{ str_pad((string) $n, $seccion['pad'], '0', STR_PAD_LEFT) }}
                                    </button>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
    
                    {{-- Nivel 3: También grande pero con búsqueda --}}
                    <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                                Nivel 3 - Centenas
                            </h2>
                            <input type="text" wire:model.live.debounce.300ms="searchNivel3" placeholder="Buscar centena..." class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 w-full max-w-xs text-sm" />
                        </div>
                        
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-10 gap-2">
                            @foreach (collect($this->numerosNivel3())->forPage($pageNivel3, $perPage) as $n)
                                @php $estado = $this->estadoDe($n, 3); @endphp
                                <button wire:click="agregarAlCarrito({{ $n }}, 3)" @disabled($estado === 'bloqueado')
                                    @class([
                                        'h-12 flex items-center justify-center rounded-lg font-bold text-sm transition-all',
                                        'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                        'bg-blue-600 text-white ring-2 ring-blue-100' => $estado === 'adquirido',
                                        'bg-gray-100 text-gray-300 cursor-not-allowed' => $estado === 'bloqueado',
                                    ])>
                                    {{ str_pad((string) $n, 3, '0', STR_PAD_LEFT) }}
                                </button>
                            @endforeach
                        </div>
                        {{-- Paginación Nivel 3 --}}
                        <div class="mt-6 flex justify-center gap-2">
                            <button wire:click="anteriorNivel3" @disabled($pageNivel3 <= 1) class="px-4 py-2 bg-white border rounded-lg disabled:opacity-30 text-sm font-semibold">Anterior</button>
                            <button wire:click="siguienteNivel3" @disabled($pageNivel3 >= (int)ceil(count($this->numerosNivel3())/$perPage)) class="px-4 py-2 bg-white border rounded-lg disabled:opacity-30 text-sm font-semibold">Siguiente</button>
                        </div>
                    </section>
    
                    {{-- NIVEL 4: DISEÑO COMPACTO CON SCROLL (EL PEDIDO DEL JEFE) --}}
                    <section class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2 mb-4">
                                <span class="w-2 h-6 bg-indigo-600 rounded-full"></span>
                                Nivel 4 - Milésimas
                            </h2>
                            <input type="text" wire:model.live.debounce.300ms="searchNivel4" placeholder="Buscar en los 10,000 números..." class="rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 w-full text-sm" />
                        </div>
    
                        {{-- CONTENEDOR CON SCROLL VERTICAL --}}
                        <div class="max-h-[500px] overflow-y-auto pr-2 custom-scrollbar border border-gray-100 rounded-xl bg-gray-50 p-4">
                            <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-12 gap-1">
                                @foreach (collect($this->numerosNivel4())->forPage($pageNivel4, $perPage) as $n)
                                    @php $estado = $this->estadoDe($n, 4); @endphp
                                    <button wire:click="agregarAlCarrito({{ $n }}, 4)" @disabled($estado === 'bloqueado')
                                        @class([
                                            'py-2 text-[10px] sm:text-xs rounded font-bold transition-all text-center',
                                            'bg-green-500 text-white hover:bg-green-600' => $estado === 'disponible',
                                            'bg-blue-600 text-white' => $estado === 'adquirido',
                                            'bg-gray-200 text-gray-400 cursor-not-allowed' => $estado === 'bloqueado',
                                        ])>
                                        {{ str_pad((string) $n, 4, '0', STR_PAD_LEFT) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mt-4 flex justify-between items-center text-xs text-gray-500 font-medium">
                            <span>Página {{ $pageNivel4 }}</span>
                            <div class="flex gap-2">
                                <button wire:click="anteriorNivel4" class="px-3 py-1 bg-white border rounded-md">Anterior</button>
                                <button wire:click="siguienteNivel4" class="px-3 py-1 bg-white border rounded-md">Siguiente</button>
                            </div>
                        </div>
                    </section>
                </div>
    
                {{-- SIDEBAR: CARRITO (RESPONSIVE) --}}
                <aside class="w-full lg:w-80 shrink-0">
                    <div class="bg-indigo-900 text-white rounded-3xl shadow-xl p-6 sticky top-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-indigo-800 rounded-lg">🛒</div>
                            <h3 class="text-xl font-bold">Reserva</h3>
                        </div>
                        
                        <div class="space-y-3 mb-6 max-h-64 overflow-y-auto custom-scrollbar">
                            @forelse ($carrito as $index => $item)
                                <div class="flex justify-between items-center bg-indigo-800/50 p-3 rounded-xl border border-indigo-700">
                                    <span class="font-mono font-bold tracking-widest text-lg">
                                        {{ str_pad((string) $item['numero'], $item['nivel'], '0', STR_PAD_LEFT) }}
                                    </span>
                                    <button type="button" wire:click="quitarDelCarrito({{ $index }})" class="text-indigo-300 hover:text-red-400 font-bold text-xs uppercase">Quitar</button>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <p class="text-indigo-300 text-sm">Tu carrito está vacío</p>
                                </div>
                            @endforelse
                        </div>
    
                        @if (count($carrito) > 0)
                            <div class="pt-4 border-t border-indigo-700">
                                <button wire:click="confirmarReserva" class="w-full py-4 bg-green-500 hover:bg-green-400 text-white rounded-2xl font-black text-lg transition shadow-lg shadow-green-900/20">
                                    CONFIRMAR AHORA
                                </button>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </div>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</div>
