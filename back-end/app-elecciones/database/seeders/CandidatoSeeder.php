<?php

namespace Database\Seeders;

use App\Models\Candidato;
use Database\Factories\CandidatoFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CandidatoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Candidato::factory()->count(3)->create();
    }
}
