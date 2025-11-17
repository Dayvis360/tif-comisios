<?php

namespace Database\Seeders;
use App\Models\Mesa;
use Database\Factories\MesaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Mesa::factory()->count(3)->create();
    }
}
