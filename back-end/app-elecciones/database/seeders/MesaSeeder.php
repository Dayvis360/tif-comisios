<?php

namespace Database\Seeders;
use App\Models\Mesa;
use App\Models\Provincia;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea mesas electorales distribuidas en todas las provincias argentinas
     */
    public function run(): void
    {
        $provincias = Provincia::all();
        $mesasPorProvincia = [
            'Buenos Aires' => 50,
            'Córdoba' => 25,
            'Santa Fe' => 25,
            'Mendoza' => 15,
            'Tucumán' => 12,
            'Entre Ríos' => 10,
            'Salta' => 10,
            'Chaco' => 8,
            'Corrientes' => 8,
            'Misiones' => 8,
            'Santiago del Estero' => 7,
            'San Juan' => 6,
            'Jujuy' => 6,
            'Río Negro' => 5,
            'Neuquén' => 5,
            'Formosa' => 5,
            'Chubut' => 4,
            'San Luis' => 4,
            'Catamarca' => 3,
            'La Rioja' => 3,
            'La Pampa' => 3,
            'Santa Cruz' => 3,
            'Tierra del Fuego' => 2,
        ];

        $totalMesas = 0;

        foreach ($provincias as $provincia) {
            $cantidadMesas = $mesasPorProvincia[$provincia->nombre] ?? 5;
            
            for ($i = 1; $i <= $cantidadMesas; $i++) {
                Mesa::create([
                    'provincia_id' => $provincia->id,
                    'circuito' => 'Circuito ' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'establecimiento' => "Escuela Nº " . $i . " - " . $provincia->nombre,
                    'electores' => rand(300, 500),
                ]);
                $totalMesas++;
            }
        }

        echo "✓ Mesas electorales creadas con éxito ({$totalMesas} mesas en 23 provincias)\n";
    }
}
