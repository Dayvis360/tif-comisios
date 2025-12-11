<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Telegrama;
use App\Repositories\TelegramaRepository;
use Illuminate\Support\Carbon;

class TelegramaTest extends TestCase
{
//tiene que pasar, crea un telegrama desde request y verifica que los campos se hayan seteado correctamente, incluyendo los valores por defecto.
    public function test_crear_desde_request_sets_fields_and_defaults()
    {
        $t = Telegrama::crearDesdeRequest(1, 2, 10, 5, 1, 0, 0, null);

        $this->assertEquals(1, $t->mesa_id);
        $this->assertEquals(2, $t->lista_id);
        $this->assertEquals(10, $t->votos_Diputados);
        $this->assertEquals(5, $t->votos_Senadores);
        $this->assertEquals(1, $t->voto_Blancos);
        $this->assertEquals(0, $t->voto_Nulos);
        $this->assertEquals(0, $t->voto_Recurridos);
        $this->assertEquals('Sistema', $t->usuario_carga);
        $this->assertNotNull($t->fecha_carga);
        $this->assertInstanceOf(Carbon::class, $t->fecha_carga);
    }
//verifica que al actualizar los datos de un telegrama, los campos se actualicen correctamente y que la fecha de modificacion se establezca adecuadamente.            
    public function test_actualizar_datos_updates_fields_and_modificacion()
    {
        $t = new Telegrama();
        $t->mesa_id = 1;
        $t->lista_id = 2;
        $t->votos_Diputados = 0;
        $t->votos_Senadores = 0;

        $t->actualizarDatos(3, 4, 7, 8, 2, 1, 0, 'tester');

        $this->assertEquals(3, $t->mesa_id);
        $this->assertEquals(4, $t->lista_id);
        $this->assertEquals(7, $t->votos_Diputados);
        $this->assertEquals(8, $t->votos_Senadores);
        $this->assertEquals(2, $t->voto_Blancos);
        $this->assertEquals(1, $t->voto_Nulos);
        $this->assertEquals(0, $t->voto_Recurridos);
        $this->assertEquals('tester', $t->usuario_modificacion);
        $this->assertNotNull($t->fecha_modificacion);
        $this->assertInstanceOf(Carbon::class, $t->fecha_modificacion);
    }
//verifica que el metodo verificarQueSeaValido lance excepciones adecuadas cuando los votos son negativos o cuando ya existe un telegrama para la misma mesa y lista.
    public function test_verificar_que_sea_valido_throws_on_negative_votes()
    {
        $repo = $this->createMock(TelegramaRepository::class);
        $repo->method('existeTelegramaParaMesaYLista')->willReturn(false);

        $fields = [
            ['votos_Diputados', 'Los votos para diputados no pueden ser negativos'],
            ['votos_Senadores', 'Los votos para senadores no pueden ser negativos'],
            ['voto_Blancos', 'Los votos en blanco no pueden ser negativos'],
            ['voto_Nulos', 'Los votos nulos no pueden ser negativos'],
            ['voto_Recurridos', 'Los votos recurridos no pueden ser negativos'],
        ];

        foreach ($fields as [$field, $message]) {
            $t = new Telegrama();
            $t->mesa_id = 1;
            $t->lista_id = 1;
            $t->votos_Diputados = 0;
            $t->votos_Senadores = 0;
            $t->voto_Blancos = 0;
            $t->voto_Nulos = 0;
            $t->voto_Recurridos = 0;

            $t->$field = -1;

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage($message);

            $t->verificarQueSeaValido($repo);
        }
    }
//verifica que el metodo verificarQueSeaValido lance excepciones adecuadas cuando los votos son negativos o cuando ya existe un telegrama para la misma mesa y lista.
    public function test_verificar_que_sea_valido_throws_when_duplicate_exists()
    {
        $repo = $this->createMock(TelegramaRepository::class);
        $repo->method('existeTelegramaParaMesaYLista')->willReturn(true);

        $t = new Telegrama();
        $t->mesa_id = 1;
        $t->lista_id = 1;
        $t->votos_Diputados = 1;
        $t->votos_Senadores = 0;
        $t->voto_Blancos = 0;
        $t->voto_Nulos = 0;
        $t->voto_Recurridos = 0;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Ya existe un telegrama para esta mesa y lista');

        $t->verificarQueSeaValido($repo);
    }
//verifica que los metodos calcularTotalVotos y tieneVotosValidos funcionen correctamente para diferentes combinaciones de votos.
    public function test_calcular_total_votos_and_tiene_votos_validos()
    {
        $t = new Telegrama();
        $t->votos_Diputados = 3;
        $t->votos_Senadores = 2;
        $t->voto_Blancos = 1;
        $t->voto_Nulos = 1;
        $t->voto_Recurridos = 0;

        $this->assertEquals(7, $t->calcularTotalVotos());
        $this->assertTrue($t->tieneVotosValidos());

        $t->votos_Diputados = 0;
        $t->votos_Senadores = 0;
        $this->assertFalse($t->tieneVotosValidos());
    }
    
//prueba para verificar que el metodo verificarQueSeaValido no lance excepciones cuando los datos son validos y no hay duplicados y de paso le puse un campo erroneo para probar si puede fallar el test
    public function test_verificar_que_sea_valido_does_not_throw_when_not_duplicate()
    {
    $repo = $this->createMock(TelegramaRepository::class);
    $repo->method('existeTelegramaParaMesaYLista')->willReturn(false);

    $t = new Telegrama();
    $t->mesa_id = 'y';
    $t->lista_id = 1;
    $t->votos_Diputados = 1;
    $t->votos_Senadores = 0;
    $t->voto_Blancos = 0;
    $t->voto_Nulos = 0;
    $t->voto_Recurridos = 0;

    $t->verificarQueSeaValido($repo);

    $this->assertTrue(true);
    }

//verifica que el metodo validarEstructuraImportacion detecte correctamente errores en la estructura de los datos de importacion, incluyendo campos faltantes, valores no numericos y valores negativos.
    public function test_validar_estructura_importacion_detects_errors()
    {
        // faltan campos 
        $err = Telegrama::validarEstructuraImportacion([]);
        $this->assertNotEmpty($err);
        $this->assertContains('Falta el campo mesa_id', $err);

        // datos no numericos 
        $dato = [
            'mesa_id' => 'x',
            'lista_id' => 'y',
            'votos_Diputados' => 'a',
            'votos_Senadores' => 'b',
            'voto_Blancos' => 'c',
            'voto_Nulos' => 'd',
            'voto_Recurridos' => 'e',
        ];

        $err = Telegrama::validarEstructuraImportacion($dato);
        $this->assertContains('El campo mesa_id debe ser numérico', $err);
        $this->assertContains('El campo lista_id debe ser numérico', $err);
        $this->assertContains('El campo votos_Diputados debe ser numérico', $err);

        // valores negativos 
        $dato2 = [
            'mesa_id' => 1,
            'lista_id' => 1,
            'votos_Diputados' => -1,
            'votos_Senadores' => -2,
            'voto_Blancos' => -3,
            'voto_Nulos' => -4,
            'voto_Recurridos' => -5,
        ];

        $err = Telegrama::validarEstructuraImportacion($dato2);
        $this->assertContains('Los votos_Diputados no pueden ser negativos', $err);
        $this->assertContains('Los votos_Senadores no pueden ser negativos', $err);
        $this->assertContains('Los voto_Blancos no pueden ser negativos', $err);
    }
}

