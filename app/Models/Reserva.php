<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $fillable = [
        'user_id',
        'numero',
        'nivel',
        'nombre_cliente',
        'telefono_cliente',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'nivel' => 'integer',
        ];
    }

    /**
     * Obtiene todas las reservas como array compatible con TerminacionService.
     *
     * @return array<int, array{numero: int, nivel: int}>
     */
    public static function comprasExistentes(): array
    {
        return static::all()
            ->map(fn (Reserva $r) => ['numero' => $r->numero, 'nivel' => $r->nivel])
            ->values()
            ->all();
    }
}
