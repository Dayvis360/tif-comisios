<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Provincia;

class ProvinciaFactory extends Factory
{
    protected $model = Provincia::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->unique()->state, 
            'bancas_diputados' => $this->faker->numberBetween(1, 50),
            'bancas_senadores' => 3,
        ];
    }
}
