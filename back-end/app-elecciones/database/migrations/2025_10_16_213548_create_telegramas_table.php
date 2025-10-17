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
            $table->timestamps();
            $table->string('usuarioCarga');
            $table->integer('votoBlanco');
            $table->integer('votoNulo');
            $table->integer('votoRecurrido');
            $table->foreignId('usuario_id')->constrained('usuarios');
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
