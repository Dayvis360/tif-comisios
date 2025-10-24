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
        ];
    }
}
