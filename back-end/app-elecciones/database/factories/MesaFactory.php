<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mesa;
use App\Models\Provincia;

class MesaFactory extends Factory
{
    protected $model = Mesa::class;

    public function definition()
    {
        return [
            'provincia_id' => Provincia::factory(),
            'circuito' => $this->faker->unique()->numerify('####'),
            'establecimiento' => 'Escuela ' . $this->faker->numberBetween(1, 100),
            'electores' => $this->faker->numberBetween(100, 1000),
        ];
    }
}
