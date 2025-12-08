<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('alianza')->nullable();
            $table->string('cargo');
            $table->foreignId('provincia_id')->constrained('provincias')->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['nombre', 'cargo', 'provincia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listas');
    }
};
