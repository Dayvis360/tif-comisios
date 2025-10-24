<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Lista;
use App\Models\Provincia;

class ListaTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_listar_listas()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Buenos Aires']);
        
        Lista::factory()->create([
            'nombre' => 'Lista 1',
            'cargo' => 'Presidente',
            'provincia_id' => $provincia->id
        ]);
        
        Lista::factory()->create([
            'nombre' => 'Lista 2',
            'cargo' => 'Gobernador',
            'provincia_id' => $provincia->id
        ]);

        $response = $this->getJson('/api/listas');

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Lista 1'])
                 ->assertJsonFragment(['nombre' => 'Lista 2']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_una_lista()
    {
        $provincia = Provincia::factory()->create(['nombre' => 'Córdoba']);
        
        $data = [
            'nombre' => 'Lista Unidad',
            'alianza' => 'Frente de Todos',
            'cargo' => 'Gobernador',
            'provincia_id' => $provincia->id
        ];

        $response = $this->postJson('/api/listas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Lista Unidad'])
                 ->assertJsonFragment(['alianza' => 'Frente de Todos']);

        $this->assertDatabaseHas('listas', ['nombre' => 'Lista Unidad']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_lista_sin_provincia_valida()
    {
        $data = [
            'nombre' => 'Lista Test',
            'cargo' => 'Presidente',
            'provincia_id' => 999 
        ];

        $response = $this->postJson('/api/listas', $data);

        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['provincia_id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_lista_sin_nombre()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'cargo' => 'Presidente',
            'provincia_id' => $provincia->id
        ];

        $response = $this->postJson('/api/listas', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nombre']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function no_puede_crear_lista_sin_cargo()
    {
        $provincia = Provincia::factory()->create();
        
        $data = [
            'nombre' => 'Lista Test',
            'provincia_id' => $provincia->id
        ];

        $response = $this->postJson('/api/listas', $data);
        $response->dump();
        $response->assertStatus(422); //error 422 para saber que en el response falta un dato
        $response->assertJsonValidationErrors(['cargo']);
    }
}