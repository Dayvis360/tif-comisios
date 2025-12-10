<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Candidato;
use App\Repositories\CandidatoRepository;
use Mockery;

class CandidatoTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_nombre_vacio()
    {
        $repositoryMock = Mockery::mock(CandidatoRepository::class);
        $repositoryMock->shouldReceive('existeOrdenEnLista')->andReturn(false);

        $candidato = new Candidato([
            'nombre' => '',
            'orden_en_lista' => 1,
            'lista_id' => 1
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $candidato->verificarQueSeaValido($repositoryMock);
    }

    public function test_puede_crear_candidato()
    {
        $candidato = new Candidato([
            'nombre' => 'Alejo Cruz',
            'orden_en_lista' => 3,
            'lista_id' => 10
        ]);

        $this->assertEquals('Alejo Cruz', $candidato->nombre);
        $this->assertEquals(3, $candidato->orden_en_lista);
        $this->assertEquals(10, $candidato->lista_id);
    }

    public function test_actualizar_datos()
    {
        $candidato = new Candidato([
            'nombre' => 'David Teran',
            'orden_en_lista' => 1,
            'lista_id' => 5
        ]);

        $candidato->actualizarDatos('Tobias Teran', 2, 6);

        $this->assertEquals('Tobias Teran', $candidato->nombre);
        $this->assertEquals(2, $candidato->orden_en_lista);
        $this->assertEquals(6, $candidato->lista_id);
    }

    public function test_orden_menor_uno()
    {
        $repositoryMock = Mockery::mock(CandidatoRepository::class);
        $repositoryMock->shouldReceive('existeOrdenEnLista')->andReturn(false);
        $candidato = new Candidato([
            'nombre' => 'Laura Gómez',
            'orden_en_lista' => 0,
            'lista_id' => 2
        ]);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("el orden en lista tiene que ser mayor a 0");

        $candidato->verificarQueSeaValido($repositoryMock);
        
    }

    public 
}
