<?php

namespace Database\Seeders;
use App\Models\Lista;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ListaSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = Provincia::all();
        
        $partidosNacionales = [
            'Unión por la Patria',
            'Juntos por el Cambio',
            'La Libertad Avanza',
            'Hacemos por Nuestro País',
            'Frente de Izquierda y de Trabajadores',
        ];

        $totalListas = 0;

        foreach ($provincias as $provincia) {
            foreach ($partidosNacionales as $partido) {
                Lista::create([
                    'nombre' => $partido,
                    'cargo' => 'DIPUTADOS',
                    'provincia_id' => $provincia->id,
                ]);
                
                Lista::create([
                    'nombre' => $partido,
                    'cargo' => 'SENADORES',
                    'provincia_id' => $provincia->id,
                ]);
                
                $totalListas += 2;
            }
        }

        echo "✓ Listas provinciales creadas con éxito ({$totalListas} listas = 23 provincias x 5 partidos x 2 cargos)\n";
    }
}
