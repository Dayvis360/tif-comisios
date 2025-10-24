<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lista;
use App\Models\Provincia;

class ListaFactory extends Factory
{
    protected $model = Lista::class;

    public function definition()
    {
        return [
            'provincia_id' => Provincia::factory(), 
            'cargo' => $this->faker->randomElement(['DIPUTADOS', 'SENADORES']),
            'nombre' => 'Lista ' . $this->faker->randomLetter(),
            'alianza' => 'Frente ' . $this->faker->randomLetter(),
        ];
    }
}

