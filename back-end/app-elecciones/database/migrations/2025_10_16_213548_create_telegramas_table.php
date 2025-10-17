<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegramas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas');
            $table->foreignId('lista_id')->constrained('listas');
            $table->integer('votos_Diputados');
            $table->integer('votos_Senadores');
            $table->integer('voto_Blanco');
            $table->integer('voto_Nulo');
            $table->integer('voto_Recurrido');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegramas');
    }
};
