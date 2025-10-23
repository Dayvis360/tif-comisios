<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Provincia;

class ProvinciaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    /** @test */
    public function puede_listar_provincias()
    {
        Provincia::factory()->create(['nombre' => 'Buenos Aires']);
        Provincia::factory()->create(['nombre' => 'Córdoba']);

        $response = $this->getJson('/api/provincias');

        $response->assertStatus(200)
                 ->assertJsonFragment(['nombre' => 'Buenos Aires'])
                 ->assertJsonFragment(['nombre' => 'Córdoba']);
    }

    /** @test */
    public function puede_crear_una_provincia()
    {
        $data = ['nombre' => 'Mendoza'];

        $response = $this->postJson('/api/provincias', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['nombre' => 'Mendoza']);

        $this->assertDatabaseHas('provincias', ['nombre' => 'Mendoza']);
    }

    /** @test */
    public function no_puede_crear_provincia_con_nombre_duplicado()
    {
        Provincia::factory()->create(['nombre' => 'Santa Fe']);

        $response = $this->postJson('/api/provincias', ['nombre' => 'Santa Fe']);

        $response->assertStatus(422); // Error de validación
        $response->assertJsonValidationErrors(['nombre']);
    }
}
