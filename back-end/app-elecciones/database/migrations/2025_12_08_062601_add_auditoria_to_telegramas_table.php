<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegramas', function (Blueprint $table) {
            $table->string('usuario_carga', 100)->nullable()->after('voto_Recurridos');
            $table->timestamp('fecha_carga')->nullable()->after('usuario_carga');
            $table->string('usuario_modificacion', 100)->nullable()->after('fecha_carga');
            $table->timestamp('fecha_modificacion')->nullable()->after('usuario_modificacion');
        });
    }

    public function down(): void
    {
        Schema::table('telegramas', function (Blueprint $table) {
            $table->dropColumn(['usuario_carga', 'fecha_carga', 'usuario_modificacion', 'fecha_modificacion']);
        });
    }
};
