<?php

namespace App\Services;

/**
 * Sistema de exclusión jerárquica por terminación.
 *
 * Niveles: 1 (unidades 0-9), 2 (decenas 00-99), 3 (centenas 000-999), 4 (unidades de mil 0000-9999).
 * Un número "hijo" (más cifras) reclama la propiedad de sus "ancestros" (menos cifras).
 */
class TerminacionService
{
    private const MAX_NIVEL = 4;

    /**
     * Obtiene los ancestros (sufijos de menor longitud) de un número.
     * Usa módulo: 1288 → [288, 88, 8] para niveles 3, 2, 1.
     *
     * @param  int  $numero  El número seleccionado (ej: 1288)
     * @param  int  $nivel  Nivel del número (1-4)
     * @return array<int, array{numero: int, nivel: int}> Lista de ancestros
     */
    public function obtenerAncestros(int $numero, int $nivel): array
    {
        $ancestros = [];

        for ($nivelPadre = $nivel - 1; $nivelPadre >= 1; $nivelPadre--) {
            $modulo = 10 ** $nivelPadre;
            $sufijo = $numero % $modulo;
            $ancestros[] = ['numero' => $sufijo, 'nivel' => $nivelPadre];
        }

        return $ancestros;
    }

    /**
     * Obtiene todos los descendientes (números de niveles superiores que terminan en este número).
     * Ej: para 8 (nivel 1) → 08,18..98 (nivel 2), 008,108..998 (nivel 3), etc.
     *
     * @param  int  $numero  El número base (ej: 8)
     * @param  int  $nivel  Nivel del número (1-4)
     * @return array<int, array{numero: int, nivel: int}> Lista de descendientes
     */
    public function obtenerDescendientes(int $numero, int $nivel): array
    {
        $descendientes = [];
        $base = 10 ** $nivel;

        for ($nivelHijo = $nivel + 1; $nivelHijo <= self::MAX_NIVEL; $nivelHijo++) {
            $factor = 10 ** ($nivelHijo - $nivel);
            for ($i = 0; $i < $factor; $i++) {
                $hijo = $numero + ($i * $base);
                $descendientes[] = ['numero' => $hijo, 'nivel' => $nivelHijo];
            }
        }

        return $descendientes;
    }

    /**
     * Verifica si un número es seleccionable.
     * Retorna false si el número, alguno de sus ancestros o alguno de sus descendientes
     * ya está en $comprasExistentes.
     *
     * @param  int  $numero  Número a comprobar
     * @param  int  $nivel  Nivel del número
     * @param  array<int, array{numero: int, nivel: int}>  $comprasExistentes  Compras ya realizadas
     * @return bool
     */
    public function esSeleccionable(int $numero, int $nivel, array $comprasExistentes): bool
    {
        foreach ($comprasExistentes as $compra) {
            $num = $compra['numero'];
            $niv = $compra['nivel'];

            // Misma compra exacta
            if ($numero === $num && $nivel === $niv) {
                return false;
            }

            // Ellos compraron un descendiente: su número termina en nuestro número
            // Ej: nosotros queremos 88, ellos tienen 1288 → 1288 % 100 = 88
            if ($niv > $nivel && ($num % (10 ** $nivel)) === $numero) {
                return false;
            }

            // Ellos compraron un ancestro: nuestro número termina en el suyo
            // Ej: nosotros queremos 88, ellos tienen 8 → 88 % 10 = 8
            if ($niv < $nivel && ($numero % (10 ** $niv)) === $num) {
                return false;
            }
        }

        return true;
    }
}
