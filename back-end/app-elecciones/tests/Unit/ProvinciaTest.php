<?php

namespace Tests\Unit;

use App\Models\Provincia;
use App\Repositories\ProvinciaRepository;
use PHPUnit\Framework\TestCase;

class ProvinciaTest extends TestCase
{
    public function test_crear_desde_request()
    {
        $provincia = Provincia::crearDesdeRequest('  Córdoba  ', 18, 3);

        $this->assertEquals('Córdoba', $provincia->nombre);
        $this->assertEquals(18, $provincia->bancas_diputados);
        $this->assertEquals(3, $provincia->bancas_senadores);
    }

    public function test_actualizar_datos()
    {
        $provincia = new Provincia([
            'nombre' => 'Santa Fe',
            'bancas_diputados' => 10,
            'bancas_senadores' => 3,
        ]);

        $provincia->actualizarDatos(' Mendoza ', 5, 2);

        $this->assertEquals('Mendoza', $provincia->nombre);
        $this->assertEquals(5, $provincia->bancas_diputados);
        $this->assertEquals(2, $provincia->bancas_senadores);
    }

    public function test_verificar_que_sea_valida_falla_si_nombre_esta_vacio()
    {
        $repo = $this->createMock(ProvinciaRepository::class);
        $repo->method('existeNombre')->willReturn(false);

        $provincia = new Provincia([
            'nombre' => '',
            'bancas_diputados' => 5,
            'bancas_senadores' => 3,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $provincia->verificarQueSeaValida($repo);
    }

    public function test_verificar_que_sea_valida_falla_si_nombre_duplicado()
    {
        $repo = $this->createMock(ProvinciaRepository::class);
        $repo->method('existeNombre')->willReturn(true);

        $provincia = new Provincia([
            'nombre' => 'Córdoba',
            'bancas_diputados' => 5,
            'bancas_senadores' => 3,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $provincia->verificarQueSeaValida($repo);
    }

    public function test_verificar_que_sea_valida_falla_si_bancas_invalidas()
    {
        $repo = $this->createMock(ProvinciaRepository::class);
        $repo->method('existeNombre')->willReturn(false);

        $provincia = new Provincia([
            'nombre' => 'Misiones',
            'bancas_diputados' => 0,
            'bancas_senadores' => 0,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $provincia->verificarQueSeaValida($repo);
    }

    public function test_obtener_descripcion_completa()
    {
        $provincia = new Provincia([
            'nombre' => 'Buenos Aires',
            'bancas_diputados' => 35,
            'bancas_senadores' => 3,
        ]);

        $this->assertEquals(
            'Buenos Aires (Diputados: 35, Senadores: 3)',
            $provincia->obtenerDescripcionCompleta()
        );
    }

    public function test_puede_distribuir_bancas()
    {
        $provincia = new Provincia([
            'bancas_diputados' => 10,
            'bancas_senadores' => 3,
        ]);

        $this->assertTrue($provincia->puedeDistribuirBancas('DIPUTADOS'));
        $this->assertTrue($provincia->puedeDistribuirBancas('SENADORES'));

        $provincia->bancas_diputados = 0;
        $provincia->bancas_senadores = 0;

        $this->assertFalse($provincia->puedeDistribuirBancas('DIPUTADOS'));
        $this->assertFalse($provincia->puedeDistribuirBancas('SENADORES'));
    }
}
