<?php

use App\Models\Reserva;
use Livewire\Volt\Component;

new class extends Component
{
    public function reservas()
    {
        return Reserva::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function mensajeValor(int $nivel): ?string
    {
        return match ($nivel) {
            1 => 'Este número te otorga control sobre 1.110 combinaciones (decenas, centenas y milésimas).',
            2 => 'Este número te otorga control sobre 110 combinaciones (centenas y milésimas).',
            3 => 'Este número te otorga control sobre 10 combinaciones (milésimas).',
            4 => 'Este número representa 1 combinación específica.',
            default => null,
        };
    }
}; ?>

<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mis Reservas</h1>

        @if ($this->reservas()->isEmpty())
            <div class="bg-gray-50 rounded-lg p-8 text-center text-gray-600">
                <p class="text-lg">Aún no tienes reservas.</p>
                <a href="{{ route('loteria') }}" wire:navigate class="mt-4 inline-block px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    ¡Haz tu primera reserva!
                </a>
            </div>
        @else
            <div class="overflow-hidden bg-white shadow-sm rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($this->reservas() as $reserva)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono font-semibold text-indigo-600">
                                        {{ str_pad((string) $reserva->numero, $reserva->nivel, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    Nivel {{ $reserva->nivel }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $reserva->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($mensaje = $this->mensajeValor($reserva->nivel))
                                        <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-1 rounded">
                                            {{ $mensaje }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <a href="{{ route('loteria') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Agregar más reservas
                </a>
            </div>
        @endif
    </div>
</div>
