<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Mesa;
use App\Repositories\MesaRepository;
use Mockery;

class MesaTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_crear_request()
    {
        $mesa = Mesa::crearDesdeRequest(
            1,
            "c-107",
            " Escuela N°52",
            450
        );
        $this->assertEquals(1, $mesa->provincia_id);
        $this->assertEquals("c-107", $mesa->circuito);
        $this->assertEquals("Escuela N°52", $mesa->establecimiento);
        $this->assertEquals(450, $mesa->electores);
    }

    public function test_actualizar_datos()
    {
        $mesa = new Mesa([
            'provincia_id' => 2,
            'circuito' => 'c-777',
            'establecimiento' => 'Escuela N°10',
            'electores' => 300
        ]);

        $mesa->actualizarDatos(3, 'c-888', 'Escuela N°20', 350);

        $this->assertEquals(3, $mesa->provincia_id);
        $this->assertEquals('c-888', $mesa->circuito);
        $this->assertEquals('Escuela N°20', $mesa->establecimiento);
        $this->assertEquals(350, $mesa->electores);
    }
}