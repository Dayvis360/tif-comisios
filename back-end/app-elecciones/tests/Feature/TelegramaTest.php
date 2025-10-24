<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Telegrama;
use App\Models\Mesa;
use App\Models\Lista;
use App\Models\Provincia;

class TelegramaTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_listar_telegramas()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Buenos Aires']);
        $mesa = Mesa::factory()->create([
            'circuito' => 'Circuito 1',
            'establecimiento' => 'Escuela Primaria N° 1',
            'electores' => 300,
            'provincia_id' => $provincia->id
        ]);
        $lista = Lista::factory()->create([
            'nombre' => 'Lista 1',
            'cargo' => 'Presidente',
            'provincia_id' => $provincia->id
        ]);
        
        Telegrama::factory()->create([
            'mesa_id' => $mesa->id,
            'lista_id' => $lista->id,
            'votos_Diputados' => 150,
            'votos_Senadores' => 140,
            'voto_Blancos' => 5,
            'voto_Nulos' => 3,
            'voto_Recurridos' => 2
        ]);

        $response = $this->getJson('/api/telegramas');

        $response->assertStatus(200)
                 ->assertJsonFragment(['votos_Diputados' => 150])
                 ->assertJsonFragment(['votos_Senadores' => 140])
                 ->assertJsonFragment(['voto_Blancos' => 5]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_un_telegrama()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Córdoba']);
        $mesa = Mesa::factory()->create([
            'circuito' => 'Circuito Centro',
            'establecimiento' => 'Universidad Nacional',
            'electores' => 500,
            'provincia_id' => $provincia->id
        ]);
        $lista = Lista::factory()->create([
            'nombre' => 'Lista Unidad',
            'cargo' => 'Gobernador',
            'provincia_id' => $provincia->id
        ]);
        
        $data = [
            'mesa_id' => $mesa->id,
            'lista_id' => $lista->id,
            'votos_Diputados' => 200,
            'votos_Senadores' => 190,
            'voto_Blancos' => 10,
            'voto_Nulos' => 5,
            'voto_Recurridos' => 3
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['votos_Diputados' => 200])
                 ->assertJsonFragment(['votos_Senadores' => 190])
                 ->assertJsonFragment(['voto_Blancos' => 10]);

        $this->assertDatabaseHas('telegramas', ['votos_Diputados' => 200]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_telegrama_sin_mesa_valida()
    {
        $provincia = Provincia::factory()->create();
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'mesa_id' => 999, // no existe esta mesa
            'lista_id' => $lista->id,
            'votos_Diputados' => 100,
            'votos_Senadores' => 90,
            'voto_Blancos' => 5,
            'voto_Nulos' => 3,
            'voto_Recurridos' => 2
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mesa_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_telegrama_sin_lista_valida()
    {
        $provincia = Provincia::factory()->create();
        $mesa = Mesa::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'mesa_id' => $mesa->id,
            'lista_id' => 999, // no existe esta lista
            'votos_Diputados' => 100,
            'votos_Senadores' => 90,
            'voto_Blancos' => 5,
            'voto_Nulos' => 3,
            'voto_Recurridos' => 2
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lista_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_telegrama_sin_votos_diputados()//esta funcion podria ser tambine viceverza con los cenadores
    {
        $provincia = Provincia::factory()->create();
        $mesa = Mesa::factory()->create(['provincia_id' => $provincia->id]);
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'mesa_id' => $mesa->id,
            'lista_id' => $lista->id,
            'votos_Senadores' => 90,
            'voto_Blancos' => 5,
            'voto_Nulos' => 3,
            'voto_Recurridos' => 2
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['votos_Diputados']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_telegrama_con_votos_negativos()
    {
        $provincia = Provincia::factory()->create();
        $mesa = Mesa::factory()->create(['provincia_id' => $provincia->id]);
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'mesa_id' => $mesa->id,
            'lista_id' => $lista->id,
            'votos_Diputados' => -10,
            'votos_Senadores' => 90,
            'voto_Blancos' => 5,
            'voto_Nulos' => 3,
            'voto_Recurridos' => 2
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['votos_Diputados']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_telegrama_con_votos_cero()
    {
        $provincia = Provincia::factory()->create();
        $mesa = Mesa::factory()->create(['provincia_id' => $provincia->id]);
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'mesa_id' => $mesa->id,
            'lista_id' => $lista->id,
            'votos_Diputados' => 0,
            'votos_Senadores' => 0,
            'voto_Blancos' => 0,
            'voto_Nulos' => 0,
            'voto_Recurridos' => 0
        ];

        $response = $this->postJson('/api/telegramas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['votos_Diputados' => 0])
                 ->assertJsonFragment(['votos_Senadores' => 0]);

        $this->assertDatabaseHas('telegramas', ['votos_Diputados' => 0]);
    }
}