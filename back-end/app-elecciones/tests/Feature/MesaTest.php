<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\App\Models\Mesa;
use Illuminate\App\Models\Provincia;


class MesaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    //use RefreshDatabase;
    /** @test */
     public function puede_crear_una_mesa_y_su_provincia_si_no_existe()
    {
        // Datos simulados de la request
        $data = [
            'provincia' => 'Buenos Aires',
            'circuito' => 'Circuito 12',
            'establecimiento' => 'Escuela N°45',
            'electores' => 350
        ];

        // Ejecuta la petición POST al endpoint del controlador
        $response = $this->postJson('/mesas', $data);

        // Verifica que se haya creado correctamente
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Mesa creada con éxito'
                     
                 ]);

        // Comprueba que existan en la base de datos
        $this->assertDatabaseHas('provincias', ['nombre' => 'Buenos Aires']);
        $this->assertDatabaseHas('mesas', [
            'circuito' => 'Circuito 12',
            'establecimiento' => 'Escuela N°45',
            'electores' => 350
        ]);
    }

    /** @test */
    public function no_permite_crear_mesa_con_datos_invalidos()
    {
        $data = [
            'provincia' => '', // inválido
            'circuito' => '',
            'establecimiento' => '',
            'electores' => 0 // inválido (min:1)
        ];

        $response = $this->postJson('/mesas', $data);

        $response->assertStatus(422); // Error de validación
        $response->assertJsonValidationErrors(['provincia', 'circuito', 'establecimiento', 'electores']);
    }
        
    
}
