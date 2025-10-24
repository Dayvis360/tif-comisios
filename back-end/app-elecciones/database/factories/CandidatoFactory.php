<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Candidato;
use App\Models\Lista;

class CandidatoFactory extends Factory
{
    protected $model = Candidato::class;

    public function definition()
    {
        return [
            'lista_id' => Lista::factory(), 
            'nombre' => $this->faker->name,
            'orden_en_lista' => $this->faker->unique()->numberBetween(1, 20),
        ];
    }
}
