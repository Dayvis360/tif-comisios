<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Candidato;
use App\Models\Lista;
use App\Models\Provincia;

class CandidatoTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_listar_candidatos()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Buenos Aires']);
        $lista = Lista::factory()->create([
            'nombre' => 'Lista 1',
            'cargo' => 'Presidente',
            'provincia_id' => $provincia->id
        ]);
        
        Candidato::factory()->create([
            'nombre' => 'Juan Pérez',
            'orden_en_lista' => 1,
            'lista_id' => $lista->id
        ]);
        
        Candidato::factory()->create([
            'nombre' => 'María García',
            'orden_en_lista' => 2,
            'lista_id' => $lista->id
        ]);

        $response = $this->getJson('/api/candidatos');

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Juan Pérez'])
                 ->assertJsonFragment(['nombre' => 'María García']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_un_candidato()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Córdoba']);
        $lista = Lista::factory()->create([
            'nombre' => 'Lista Unidad',
            'cargo' => 'Gobernador',
            'provincia_id' => $provincia->id
        ]);
        
        $data = [
            'nombre' => 'Carlos López',
            'orden_en_lista' => 1,
            'lista_id' => $lista->id
        ];

        $response = $this->postJson('/api/candidatos', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Carlos López'])
                 ->assertJsonFragment(['orden_en_lista' => 1]);

        $this->assertDatabaseHas('candidatos', ['nombre' => 'Carlos López']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_candidato_sin_lista_valida()
    {
        $data = [
            'nombre' => 'Candidato Test',
            'orden_en_lista' => 1,
            'lista_id' => 999 
        ];

        $response = $this->postJson('/api/candidatos', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lista_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_candidato_sin_nombre()
    {
        $provincia = Provincia::factory()->create();
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'orden_en_lista' => 1,
            'lista_id' => $lista->id
        ];

        $response = $this->postJson('/api/candidatos', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_candidato_sin_orden_en_lista()
    {
        $provincia = Provincia::factory()->create();
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'nombre' => 'Candidato Test',
            'lista_id' => $lista->id
        ];

        $response = $this->postJson('/api/candidatos', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['orden_en_lista']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_candidato_con_orden_negativo()
    {
        $provincia = Provincia::factory()->create();
        $lista = Lista::factory()->create(['provincia_id' => $provincia->id]);
        
        $data = [
            'nombre' => 'Candidato Test',
            'orden_en_lista' => -1,
            'lista_id' => $lista->id
        ];

        $response = $this->postJson('/api/candidatos', $data);
        $response->dump();
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['orden_en_lista']);
    }
}