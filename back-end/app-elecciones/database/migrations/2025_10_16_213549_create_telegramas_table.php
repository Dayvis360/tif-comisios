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
            $table->foreignId('mesa_id')->constrained('mesas')->cascadeOnDelete();
            $table->foreignId('lista_id')->constrained('listas');
            $table->integer('votos_Diputados')->default(0);
            $table->integer('votos_Senadores')->default(0);
            $table->integer('voto_Blancos')->default(0);
            $table->integer('voto_Nulos')->default(0);
            $table->integer('voto_Recurridos')->default(0);
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
