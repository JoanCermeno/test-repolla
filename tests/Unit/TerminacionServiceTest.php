<?php

namespace Tests\Unit;

use App\Services\TerminacionService;
use PHPUnit\Framework\TestCase;

class TerminacionServiceTest extends TestCase
{
    private TerminacionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TerminacionService;
    }

    public function test_obtener_ancestros_para_1288_nivel_4(): void
    {
        $ancestros = $this->service->obtenerAncestros(1288, 4);

        $this->assertCount(3, $ancestros);
        $this->assertEquals(['numero' => 288, 'nivel' => 3], $ancestros[0]);
        $this->assertEquals(['numero' => 88, 'nivel' => 2], $ancestros[1]);
        $this->assertEquals(['numero' => 8, 'nivel' => 1], $ancestros[2]);
    }

    public function test_obtener_ancestros_para_88_nivel_2(): void
    {
        $ancestros = $this->service->obtenerAncestros(88, 2);

        $this->assertCount(1, $ancestros);
        $this->assertEquals(['numero' => 8, 'nivel' => 1], $ancestros[0]);
    }

    public function test_obtener_ancestros_para_8_nivel_1(): void
    {
        $ancestros = $this->service->obtenerAncestros(8, 1);

        $this->assertCount(0, $ancestros);
    }

    public function test_obtener_descendientes_para_8_nivel_1(): void
    {
        $desc = $this->service->obtenerDescendientes(8, 1);

        // Nivel 2: 8, 18, 28... 98 = 10
        // Nivel 3: 8, 108, 208... 998 = 100
        // Nivel 4: 8, 1008, 2008... 9998 = 1000
        $this->assertCount(1110, $desc);

        $nivel2 = array_filter($desc, fn ($d) => $d['nivel'] === 2);
        $this->assertCount(10, $nivel2);
        $this->assertContains(['numero' => 8, 'nivel' => 2], $desc);
        $this->assertContains(['numero' => 98, 'nivel' => 2], $desc);

        $nivel4 = array_filter($desc, fn ($d) => $d['nivel'] === 4);
        $this->assertCount(1000, $nivel4);
        $this->assertContains(['numero' => 1288, 'nivel' => 4], $desc);
    }

    public function test_obtener_descendientes_para_88_nivel_2(): void
    {
        $desc = $this->service->obtenerDescendientes(88, 2);

        // Nivel 3: 88, 188... 988 = 10
        // Nivel 4: 88, 188... 9988 = 100
        $this->assertCount(110, $desc);
        $this->assertContains(['numero' => 288, 'nivel' => 3], $desc);
        $this->assertContains(['numero' => 1288, 'nivel' => 4], $desc);
    }

    public function test_es_seleccionable_sin_compras(): void
    {
        $this->assertTrue($this->service->esSeleccionable(88, 2, []));
        $this->assertTrue($this->service->esSeleccionable(8, 1, []));
    }

    public function test_es_seleccionable_falso_si_ancestro_comprado(): void
    {
        // Alguien compró 8 (nivel 1) → 88 no es seleccionable
        $compras = [['numero' => 8, 'nivel' => 1]];
        $this->assertFalse($this->service->esSeleccionable(88, 2, $compras));
    }

    public function test_es_seleccionable_falso_si_descendiente_comprado(): void
    {
        // Alguien compró 1288 (nivel 4) → 88 no es seleccionable
        $compras = [['numero' => 1288, 'nivel' => 4]];
        $this->assertFalse($this->service->esSeleccionable(88, 2, $compras));
    }

    public function test_es_seleccionable_falso_si_8_comprado_no_puedo_comprar_88(): void
    {
        $compras = [['numero' => 8, 'nivel' => 1]];
        $this->assertFalse($this->service->esSeleccionable(88, 2, $compras));
    }

    public function test_es_seleccionable_falso_si_88_comprado_no_puedo_comprar_8(): void
    {
        $compras = [['numero' => 88, 'nivel' => 2]];
        $this->assertFalse($this->service->esSeleccionable(8, 1, $compras));
    }

    public function test_es_seleccionable_falso_si_88_comprado_no_puedo_comprar_1288(): void
    {
        $compras = [['numero' => 88, 'nivel' => 2]];
        $this->assertFalse($this->service->esSeleccionable(1288, 4, $compras));
    }

    public function test_es_seleccionable_verdadero_si_numero_independiente(): void
    {
        // Tengo 88 comprado, el 77 sigue disponible
        $compras = [['numero' => 88, 'nivel' => 2]];
        $this->assertTrue($this->service->esSeleccionable(77, 2, $compras));
        $this->assertTrue($this->service->esSeleccionable(7, 1, $compras));
    }

    public function test_es_seleccionable_falso_para_compra_exacta_duplicada(): void
    {
        $compras = [['numero' => 88, 'nivel' => 2]];
        $this->assertFalse($this->service->esSeleccionable(88, 2, $compras));
    }
}
