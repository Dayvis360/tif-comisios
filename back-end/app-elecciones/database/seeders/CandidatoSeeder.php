<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\Lista;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class CandidatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea candidatos para las listas de Buenos Aires como ejemplo
     */
    public function run(): void
    {
        // Solo crear candidatos para Buenos Aires como ejemplo
        $buenosAires = Provincia::where('nombre', 'Buenos Aires')->first();
        
        if (!$buenosAires) {
            echo "Advertencia: No se encontró Buenos Aires\n";
            return;
        }

        $candidatosPorLista = [
            // Unión por la Patria - DIPUTADOS
            'Unión por la Patria_DIPUTADOS' => [
                ['nombre' => 'Sergio Massa', 'orden' => 1],
                ['nombre' => 'Victoria Tolosa Paz', 'orden' => 2],
                ['nombre' => 'Máximo Kirchner', 'orden' => 3],
                ['nombre' => 'Cecilia Moreau', 'orden' => 4],
                ['nombre' => 'Eduardo Valdés', 'orden' => 5],
                ['nombre' => 'Vanesa Siley', 'orden' => 6],
                ['nombre' => 'Daniel Gollan', 'orden' => 7],
            ],
            // Juntos por el Cambio - DIPUTADOS
            'Juntos por el Cambio_DIPUTADOS' => [
                ['nombre' => 'Patricia Bullrich', 'orden' => 1],
                ['nombre' => 'Diego Santilli', 'orden' => 2],
                ['nombre' => 'María Eugenia Vidal', 'orden' => 3],
                ['nombre' => 'Cristian Ritondo', 'orden' => 4],
                ['nombre' => 'Silvia Lospennato', 'orden' => 5],
                ['nombre' => 'Facundo Manes', 'orden' => 6],
                ['nombre' => 'Graciela Ocaña', 'orden' => 7],
            ],
            // La Libertad Avanza - DIPUTADOS
            'La Libertad Avanza_DIPUTADOS' => [
                ['nombre' => 'Javier Milei', 'orden' => 1],
                ['nombre' => 'Victoria Villarruel', 'orden' => 2],
                ['nombre' => 'José Luis Espert', 'orden' => 3],
                ['nombre' => 'Carolina Piparo', 'orden' => 4],
                ['nombre' => 'Gabriel Bornoroni', 'orden' => 5],
                ['nombre' => 'Ramiro Marra', 'orden' => 6],
                ['nombre' => 'Lilia Lemoine', 'orden' => 7],
            ],
            // Hacemos por Nuestro País - DIPUTADOS
            'Hacemos por Nuestro País_DIPUTADOS' => [
                ['nombre' => 'Florencio Randazzo', 'orden' => 1],
                ['nombre' => 'Graciela Camaño', 'orden' => 2],
                ['nombre' => 'Alejandro Rodríguez', 'orden' => 3],
                ['nombre' => 'Margarita Stolbizer', 'orden' => 4],
                ['nombre' => 'Emilio Monzó', 'orden' => 5],
            ],
            // Frente de Izquierda - DIPUTADOS
            'Frente de Izquierda y de Trabajadores_DIPUTADOS' => [
                ['nombre' => 'Nicolás del Caño', 'orden' => 1],
                ['nombre' => 'Myriam Bregman', 'orden' => 2],
                ['nombre' => 'Romina Del Plá', 'orden' => 3],
                ['nombre' => 'Christian Castillo', 'orden' => 4],
                ['nombre' => 'Cele Fierro', 'orden' => 5],
            ],
            // Unión por la Patria - SENADORES
            'Unión por la Patria_SENADORES' => [
                ['nombre' => 'Cristina Fernández de Kirchner', 'orden' => 1],
                ['nombre' => 'Axel Kicillof', 'orden' => 2],
                ['nombre' => 'Jorge Taiana', 'orden' => 3],
            ],
            // Juntos por el Cambio - SENADORES
            'Juntos por el Cambio_SENADORES' => [
                ['nombre' => 'Horacio Rodríguez Larreta', 'orden' => 1],
                ['nombre' => 'Patricia Bullrich', 'orden' => 2],
                ['nombre' => 'Luis Petri', 'orden' => 3],
            ],
            // La Libertad Avanza - SENADORES
            'La Libertad Avanza_SENADORES' => [
                ['nombre' => 'Victoria Villarruel', 'orden' => 1],
                ['nombre' => 'Javier Milei', 'orden' => 2],
                ['nombre' => 'José Luis Espert', 'orden' => 3],
            ],
            // Hacemos por Nuestro País - SENADORES
            'Hacemos por Nuestro País_SENADORES' => [
                ['nombre' => 'Florencio Randazzo', 'orden' => 1],
                ['nombre' => 'Graciela Camaño', 'orden' => 2],
                ['nombre' => 'Emilio Monzó', 'orden' => 3],
            ],
            // Frente de Izquierda - SENADORES
            'Frente de Izquierda y de Trabajadores_SENADORES' => [
                ['nombre' => 'Nicolás del Caño', 'orden' => 1],
                ['nombre' => 'Myriam Bregman', 'orden' => 2],
                ['nombre' => 'Christian Castillo', 'orden' => 3],
            ],
        ];

        $listas = Lista::where('provincia_id', $buenosAires->id)->get();
        $totalCandidatos = 0;

        foreach ($listas as $lista) {
            $clave = $lista->nombre . '_' . $lista->cargo;
            $candidatos = $candidatosPorLista[$clave] ?? [];

            foreach ($candidatos as $candidato) {
                Candidato::create([
                    'lista_id' => $lista->id,
                    'nombre' => $candidato['nombre'],
                    'orden_en_lista' => $candidato['orden'],
                ]);
                $totalCandidatos++;
            }
        }

        echo "✓ Candidatos creados para Buenos Aires ({$totalCandidatos} candidatos)\n";
    }
}
