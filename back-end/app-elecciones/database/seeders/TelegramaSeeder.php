<?php

namespace Database\Seeders;
use App\Models\Telegrama;
use Database\Factories\TelegramaFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TelegramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Telegrama::factory()->count(3)->create();
    }
}
