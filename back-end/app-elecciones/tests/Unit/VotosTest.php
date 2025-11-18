<?php

namespace Tests\Unit;
use App\Models\Telegrama;
use App\Models\Candidato;
use App\Models\Lista;
use App\Models\Mesa;
use App\Models\Provincia;
use App\Http\Controllers\VotoController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase; // Cambiado para extender la clase correcta

class VotosTest extends TestCase
{
     use RefreshDatabase;

    public function testMetodoVotos()
    {
        // Crear una lista y asociarla al candidato
        $lista = Lista::factory()->create();
        $candidato = Candidato::factory()->create([
            'nombre' => 'Juan Perez',
            'lista_id' => $lista->id
        ]);
        $mesa = Mesa::factory()->create();

        /*
        $telegrama = Telegrama::factory()->create([
            'lista_id' => $lista->id
        ]);
        */

        $telegrama = Telegrama::create([
            'mesa_id' => $mesa->id, 
            'lista_id' => $lista->id,
            'votos_Diputados' => 10,
            'votos_Senadores' => 5,
            'voto_Blancos' => 2,
            'voto_Nulos' => 1,
            'voto_Recurridos' => 0
        ]);

        // Instanciar el controlador
        $controller = new VotoController();

        // Llamar directamente al método
        $response = $controller->votosPorNombre('Juan Perez');//si noe s juan perez falla
        $data = $response->getData();

        // Verificar que la respuesta contiene el telegrama esperado
        $this->assertCount(1, $data); //verifica que solo sea una respuesta de 1 array con todos los datos de un teñegrama 
        dump($data);//para iprimir los datos obtenidos

        //para consultar que  los campode del registro sean los esperados
        $this->assertEquals($telegrama->id, $data[0]->id);
        $this->assertEquals($telegrama->lista_id, $data[0]->lista_id);
        $this->assertEquals($telegrama->mesa_id, $data[0]->mesa_id);
        $this->assertEquals($telegrama->votos_Diputados, $data[0]->votos_Diputados);
        $this->assertEquals($telegrama->votos_Senadores, $data[0]->votos_Senadores);
        $this->assertEquals($telegrama->voto_Blancos, $data[0]->voto_Blancos);
        $this->assertEquals($telegrama->voto_Nulos, $data[0]->voto_Nulos);
        $this->assertEquals($telegrama->voto_Recurridos, $data[0]->voto_Recurridos);

    }
}