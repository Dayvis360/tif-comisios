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
            'votos_Diputados' => $this->faker->numberBetween(0, 300),
            'votos_Senadores' => $this->faker->numberBetween(0, 300),
            'voto_Blancos' => $this->faker->numberBetween(0, 20),
            'voto_Nulos' => $this->faker->numberBetween(0, 10),
            'voto_Recurridos' => $this->faker->numberBetween(0, 5),
        ];
    }
}
