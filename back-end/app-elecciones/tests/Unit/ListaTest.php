<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Lista;
use App\Repositories\ListaRepository;
use Mockery;

class ListaTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_actualizar_lista()
    {
        $lista = new Lista([
            'nombre' => 'Lista 1',
            'alianza' => 'Alianza A',
            'cargo' => 'Diputado',
            'provincia_id' => 3
        ]);

        $lista->actualizarDatos('  Lista Actualizada  ', '  senador ', 5, '  Alianza B  ');

        $this->assertEquals('Lista Actualizada', $lista->nombre);
        $this->assertEquals('SENADOR', $lista->cargo);
        $this->assertEquals(5, $lista->provincia_id);
        $this->assertEquals('Alianza B', $lista->alianza);
    }

    public function test_ev_nombre_vacio()
    {
        $repoMock = Mockery::mock(ListaRepository::class);
        $repoMock->shouldReceive('existeNombreEnProvincia')->andReturn(false);

        $lista = Lista::crearDesdeRequest('', 'DIPUTADOS', 2, 'Alianza X');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('El nombre de la lista no puede estar vacío');

        $lista->verificarQueSeaValida($repoMock);
    }

    public function test_ev_cargo_invalido()
    {
        $repoMock = Mockery::mock(ListaRepository::class);
        $repoMock->shouldReceive('existeListaEnProvincia')->andReturn(false);

        $lista = new Lista([
            'nombre' => 'Lista Verde',
            'cargo' => 'PRESIDENTE',
            'provincia_id' => 1
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("El cargo debe ser DIPUTADOS o SENADORES");

        $lista->verificarQueSeaValida($repoMock);
    }

    public function test_ev_lista_duplicada()
    {
        $repoMock = Mockery::mock(ListaRepository::class);
        $repoMock->shouldReceive('existeListaEnProvincia')->andReturn(true);

        $lista = new Lista([
            'nombre' => 'Frente Popular',
            'cargo' => 'DIPUTADOS',
            'provincia_id' => 3
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Ya existe una lista 'Frente Popular' para DIPUTADOS en esta provincia");

        $lista->verificarQueSeaValida($repoMock);
    }

    public function test_ev_lista_valida()
    {
        $repoMock = Mockery::mock(ListaRepository::class);
        $repoMock->shouldReceive('existeListaEnProvincia')->andReturn(false);

        $lista = new Lista([
            'nombre' => 'Frente Azul',
            'cargo' => 'SENADORES',
            'provincia_id' => 2
        ]);

        $lista->verificarQueSeaValida($repoMock);

        $this->assertTrue(true);
    }

    public function test_no_se_puede_eliminar_si_tiene_candidatos()
    {
        $lista = Mockery::mock(Lista::class)->makePartial();

        $candidatosRelation = Mockery::mock();
        $candidatosRelation->shouldReceive('exists')->andReturn(true);
        $lista->shouldReceive('candidatos')->andReturn($candidatosRelation);

        $telegramasRelation = Mockery::mock();
        $telegramasRelation->shouldReceive('exists')->andReturn(false);
        $lista->shouldReceive('telegramas')->andReturn($telegramasRelation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No se puede eliminar la lista porque tiene candidatos asociados");

        $lista->verificarQueSeaPuedeEliminar();
    }

    public function test_no_se_puede_eliminar_si_tiene_telegramas()
    {
        $lista = Mockery::mock(Lista::class)->makePartial();

        $candidatosRelation = Mockery::mock();
        $candidatosRelation->shouldReceive('exists')->andReturn(false);
        $lista->shouldReceive('candidatos')->andReturn($candidatosRelation);

        $telegramasRelation = Mockery::mock();
        $telegramasRelation->shouldReceive('exists')->andReturn(true);
        $lista->shouldReceive('telegramas')->andReturn($telegramasRelation);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No se puede eliminar la lista porque tiene telegramas asociados");

        $lista->verificarQueSeaPuedeEliminar();
    }

    public function test_se_puede_eliminar_si_no_tiene_asociaciones()
    {
        $lista = Mockery::mock(Lista::class)->makePartial();

        $emptyRelation = Mockery::mock();
        $emptyRelation->shouldReceive('exists')->andReturn(false);

        $lista->shouldReceive('candidatos')->andReturn($emptyRelation);
        $lista->shouldReceive('telegramas')->andReturn($emptyRelation);

        $lista->verificarQueSeaPuedeEliminar();

        $this->assertTrue(true);
    }
}