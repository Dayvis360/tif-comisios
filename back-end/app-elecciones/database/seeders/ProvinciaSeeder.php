<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciaSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            ['nombre' => 'Buenos Aires', 'bancas_diputados' => 7, 'bancas_senadores' => 3],
            ['nombre' => 'Córdoba', 'bancas_diputados' => 5, 'bancas_senadores' => 3],
            ['nombre' => 'Santa Fe', 'bancas_diputados' => 5, 'bancas_senadores' => 3],
            ['nombre' => 'Mendoza', 'bancas_diputados' => 3, 'bancas_senadores' => 3],
            ['nombre' => 'Tucumán', 'bancas_diputados' => 3, 'bancas_senadores' => 3],
            ['nombre' => 'Entre Ríos', 'bancas_diputados' => 3, 'bancas_senadores' => 3],
            ['nombre' => 'Salta', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Chaco', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Corrientes', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Misiones', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Santiago del Estero', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'San Juan', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Jujuy', 'bancas_diputados' => 2, 'bancas_senadores' => 3],
            ['nombre' => 'Río Negro', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Neuquén', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Formosa', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Chubut', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'San Luis', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Catamarca', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'La Rioja', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'La Pampa', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Santa Cruz', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
            ['nombre' => 'Tierra del Fuego', 'bancas_diputados' => 1, 'bancas_senadores' => 3],
        ];

        foreach ($provincias as $provincia) {
            Provincia::create($provincia);
        }

        echo "✓ Provincias argentinas creadas con éxito (23 provincias)\n";
    }
}
