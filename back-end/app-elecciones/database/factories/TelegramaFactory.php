<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Telegrama;
use App\Models\Mesa;
use App\Models\Lista;

class TelegramaFactory extends Factory
{
    protected $model = Telegrama::class;

    public function definition()
    {
        return [
            'mesa_id' => Mesa::factory(),
            'lista_id' => Lista::factory(),
            'votos_diputados' => $this->faker->numberBetween(0, 300),
            'votos_senadores' => $this->faker->numberBetween(0, 300),
            'blancos' => $this->faker->numberBetween(0, 20),
            'nulos' => $this->faker->numberBetween(0, 10),
            'recurridos' => $this->faker->numberBetween(0, 5),
        ];
    }
}
