<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Mesa;
use App\Models\Provincia;

class MesaTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_listar_mesas()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Buenos Aires']);
        
        Mesa::factory()->create([
            'circuito' => 'Circuito 1',
            'establecimiento' => 'Escuela Primaria N° 1',
            'electores' => 300,
            'provincia_id' => $provincia->id
        ]);
        
        Mesa::factory()->create([
            'circuito' => 'Circuito 2',
            'establecimiento' => 'Escuela Secundaria N° 2',
            'electores' => 250,
            'provincia_id' => $provincia->id
        ]);

        $response = $this->getJson('/api/mesas');

        $response->assertStatus(200)
                 ->assertJsonFragment(['circuito' => 'Circuito 1'])
                 ->assertJsonFragment(['establecimiento' => 'Escuela Primaria N° 1'])
                 ->assertJsonFragment(['electores' => 300]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_una_mesa()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Córdoba']);
        
        $data = [
            'provincia_id' => $provincia->id,
            'circuito' => 'Circuito Centro',
            'establecimiento' => 'Universidad Nacional de Córdoba',
            'electores' => 500
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['circuito' => 'Circuito Centro'])
                 ->assertJsonFragment(['establecimiento' => 'Universidad Nacional de Córdoba'])
                 ->assertJsonFragment(['electores' => 500]);

        $this->assertDatabaseHas('mesas', ['circuito' => 'Circuito Centro']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_sin_provincia_valida()
    {
        $data = [
            'provincia_id' => 999,// no esxiste esta provincia
            'circuito' => 'Circuito Test',
            'establecimiento' => 'Escuela Test',
            'electores' => 100
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['provincia_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_sin_circuito()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'provincia_id' => $provincia->id,
            'establecimiento' => 'Escuela Test',
            'electores' => 100
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['circuito']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_sin_establecimiento()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'provincia_id' => $provincia->id,
            'circuito' => 'Circuito Test',
            'electores' => 100
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['establecimiento']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_sin_electores()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'provincia_id' => $provincia->id,
            'circuito' => 'Circuito Test',
            'establecimiento' => 'Escuela Test'
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['electores']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_con_electores_negativos()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'provincia_id' => $provincia->id,
            'circuito' => 'Circuito Test',
            'establecimiento' => 'Escuela Test',
            'electores' => -10
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['electores']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_mesa_con_electores_cero()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'provincia_id' => $provincia->id,
            'circuito' => 'Circuito Test',
            'establecimiento' => 'Escuela Test',
            'electores' => 0
        ];

        $response = $this->postJson('/api/mesas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['electores']);
    }
}