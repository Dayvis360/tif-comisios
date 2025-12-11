<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Mesa;
use Mockery;
//use Illuminate\Foundation\Testing\RefreshDatabase;
class MesaTest extends TestCase
{
    //use RefreshDatabase;
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


    public function test_estructura_valida(){

        $mesa = new Mesa([
            'provincia_id' => 1,
            'circuito' => 'c-123',
            'establecimiento' => 'Escuela N°5',
            'electores' => -2
        ]);
        $this->expectException(\InvalidArgumentException::class);
        $mesa->verificarQueSeaValida(Mockery::mock('App\Repositories\MesaRepository'));
        
    }
    


    public function test_verificar_Que_Sea_Puede_Eliminar(){
        $mesa = Mockery::spy(Mesa::class);
    $mesa->shouldReceive('telegramas->exists')->andReturn(false);//Simula que no tiene telegramas asociados
    
    $mesa->verificarQueSeaPuedeEliminar();//si el res devuelve true pasa
    $this->assertTrue(true);//espera que no lance excepción
    }
    
    

    public function test_obtener_descripcion_completa()
    {
        $mesa = new Mesa([
            'circuito' => 'c-456',
            'establecimiento' => 'Escuela N°15',
            'electores' => 400
        ]);

        $descripcion = $mesa->obtenerDescripcionCompleta();

        $this->assertEquals('Mesa c-456 - Escuela N°15 (400 electores)', $descripcion);
    }
}

    
