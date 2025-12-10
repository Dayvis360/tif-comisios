<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Candidato;
use App\Repositories\CandidatoRepository;
use Mockery;

class CandidatoTest extends TestCase

{
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
}
