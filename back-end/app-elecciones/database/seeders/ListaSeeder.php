<?php

namespace Database\Seeders;
use App\Models\Lista;
use Database\Factories\ListaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Lista::factory()->count(3)->create();
    }
}
