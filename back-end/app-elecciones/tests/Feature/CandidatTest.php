<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\Lista;
use App\Models\Mesa;
use App\Models\Provincia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CandidatTest extends TestCase
{
    use RefreshDatabase, WithFaker;// lo tengoq que sacar si queiro ver la base de datos, pero si lo saco despues de la ejecucion del test 2 veces falla (test_electores_duplicate)
    
    /**
     * Test para crear registros de candidato, mesa y provincia exitosamente
     */
    public function test_crear_registros_eleccion_exitosamente(): void
    {
        // Datos de prueba
        $datos = [
            'provincia' => 'Buenos Aires',
            'mesa' => [
                'circuito' => 'Circuito 1',
                'establecimiento' => 'Escuela Primaria N° 123',
                'electores' => 500
            ],
            'lista' => [
                'cargo' => 'Presidente',
                'nombre_lista' => 'Lista Unidad Ciudadana',
                'alianza' => 'Frente de Todos'
            ],
            'candidato' => [
                'nombre' => 'Juan Pérez',
                'orden_en_lista' => 1
            ]
        ];

        // Hacer la petición POST
        $response = $this->postJson('/elecciones/crear', $datos);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Registros creados exitosamente'
                ]);

        // Verificar que los datos se guardaron en la base de datos
        $this->assertDatabaseHas('provincias', [
            'nombre' => 'Buenos Aires'
        ]);

        $this->assertDatabaseHas('mesas', [
            'circuito' => 'Circuito 1',
            'establecimiento' => 'Escuela Primaria N° 123',
            'electores' => 500
        ]);

        $this->assertDatabaseHas('listas', [
            'cargo' => 'Presidente',
            'nombre_lista' => 'Lista Unidad Ciudadana',
            'alianza' => 'Frente de Todos'
        ]);

        $this->assertDatabaseHas('candidatos', [
            'nombre' => 'Juan Pérez',
            'orden_en_lista' => 1
        ]);

        // Verificar que las relaciones se establecieron correctamente
        $provincia = Provincia::where('nombre', 'Buenos Aires')->first();
        $mesa = Mesa::where('circuito', 'Circuito 1')->first();
        $lista = Lista::where('nombre_lista', 'Lista Unidad Ciudadana')->first();
        $candidato = Candidato::where('nombre', 'Juan Pérez')->first();

        $this->assertEquals($provincia->id, $mesa->provincia_id);
        $this->assertEquals($provincia->id, $lista->provincia_id);
        $this->assertEquals($lista->id, $candidato->lista_id);
    }

    /**
     * Test para validar que faltan datos requeridos
     */
    public function test_validacion_datos_faltantes(): void
    {
        // Datos incompletos
        $datos = [
            'provincia' => 'Buenos Aires',
            'mesa' => [
                'circuito' => 'Circuito 1',
                // Faltan establecimiento y electores
            ],
            'lista' => [
                'cargo' => 'Presidente',
                // Faltan nombre_lista y alianza
            ],
            'candidato' => [
                'nombre' => 'Juan Pérez',
                // Falta orden_en_lista
            ]
        ];

        // Hacer la petición POST
        $response = $this->postJson('/elecciones/crear', $datos);

        // Verificar que la respuesta sea de error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'mesa.establecimiento',
                    'mesa.electores',
                    'lista.nombre_lista',
                    'lista.alianza',
                    'candidato.orden_en_lista'
                ]);
    }

    /**
     * Test para verificar que no se crean registros duplicados de provincia
     */
    public function test_no_crear_provincia_duplicada(): void
    {
        // Crear una provincia primero
        $provincia = Provincia::create(['nombre' => 'Buenos Aires']);

        // Datos con la misma provincia
        $datos = [
            'provincia' => 'Buenos Aires',
            'mesa' => [
                'circuito' => 'Circuito 2',
                'establecimiento' => 'Escuela Secundaria N° 456',
                'electores' => 300
            ],
            'lista' => [
                'cargo' => 'Gobernador',
                'nombre_lista' => 'Lista Cambiemos',
                'alianza' => 'Juntos por el Cambio'
            ],
            'candidato' => [
                'nombre' => 'María González',
                'orden_en_lista' => 1
            ]
        ];

        // Hacer la petición POST
        $response = $this->postJson('/elecciones/crear', $datos);

        // Verificar que la respuesta sea exitosa
        $response->assertStatus(201);

        // Verificar que solo existe una provincia con ese nombre
        $this->assertEquals(1, Provincia::where('nombre', 'Buenos Aires')->count());

        // Verificar que la mesa se creó con la provincia existente
        $mesa = Mesa::where('circuito', 'Circuito 2')->first();
        $this->assertEquals($provincia->id, $mesa->provincia_id);
    }

    /**
     * Test para verificar la estructura de la respuesta JSON
     */
    public function test_estructura_respuesta_json(): void
    {
        $datos = [
            'provincia' => 'Córdoba',
            'mesa' => [
                'circuito' => 'Circuito 3',
                'establecimiento' => 'Universidad Nacional',
                'electores' => 800
            ],
            'lista' => [
                'cargo' => 'Intendente',
                'nombre_lista' => 'Lista Verde',
                'alianza' => 'Frente Verde'
            ],
            'candidato' => [
                'nombre' => 'Carlos López',
                'orden_en_lista' => 2
            ]
        ];

        $response = $this->postJson('/elecciones/crear', $datos);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'provincia' => [
                            'id',
                            'nombre',
                            'created_at',
                            'updated_at'
                        ],
                        'mesa' => [
                            'id',
                            'provincia_id',
                            'circuito',
                            'establecimiento',
                            'electores',
                            'created_at',
                            'updated_at'
                        ],
                        'lista' => [
                            'id',
                            'provincia_id',
                            'cargo',
                            'nombre_lista',
                            'alianza',
                            'created_at',
                            'updated_at'
                        ],
                        'candidato' => [
                            'id',
                            'lista_id',
                            'nombre',
                            'orden_en_lista',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]);
    }
    public function test_verificar_base_datos()
    {
    echo "Base de datos: " . config('database.connections.mysql.database');
    echo "Conexión: " . config('database.default');
    }
}
