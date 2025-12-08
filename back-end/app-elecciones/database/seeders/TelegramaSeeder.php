<?php

namespace Database\Seeders;
use App\Models\Telegrama;
use App\Models\Mesa;
use App\Models\Lista;
use Illuminate\Database\Seeder;

class TelegramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea telegramas de ejemplo con votos realistas para demostrar:
     * - Método D'Hont para DIPUTADOS
     * - Sistema 2-1 para SENADORES
     */
    public function run(): void
    {
        // Obtener la primera provincia con bancas
        $provincia = \App\Models\Provincia::whereNotNull('bancas_diputados')->first();
        
        if (!$provincia) {
            echo "Advertencia: No se encontraron provincias con bancas definidas\n";
            return;
        }

        echo "Generando telegramas para: {$provincia->nombre} ({$provincia->bancas_diputados} bancas diputados, {$provincia->bancas_senadores} bancas senadores)\n";

        // Obtener o crear mesas para la provincia
        $mesa1 = Mesa::firstOrCreate(
            ['circuito' => 'Circuito 1', 'provincia_id' => $provincia->id],
            ['establecimiento' => 'Escuela Primaria N° 1', 'electores' => 350]
        );

        $mesa2 = Mesa::firstOrCreate(
            ['circuito' => 'Circuito 2', 'provincia_id' => $provincia->id],
            ['establecimiento' => 'Escuela Primaria N° 2', 'electores' => 400]
        );

        $mesa3 = Mesa::firstOrCreate(
            ['circuito' => 'Circuito 3', 'provincia_id' => $provincia->id],
            ['establecimiento' => 'Escuela Secundaria N° 1', 'electores' => 380]
        );

        // ============ CREAR LISTAS PARA DIPUTADOS ============
        $listaA = Lista::firstOrCreate(
            ['nombre' => 'Frente de Todos', 'cargo' => 'DIPUTADOS', 'provincia_id' => $provincia->id],
            ['alianza' => 'FDT']
        );

        $listaB = Lista::firstOrCreate(
            ['nombre' => 'Juntos por el Cambio', 'cargo' => 'DIPUTADOS', 'provincia_id' => $provincia->id],
            ['alianza' => 'JxC']
        );

        $listaC = Lista::firstOrCreate(
            ['nombre' => 'Libertad Avanza', 'cargo' => 'DIPUTADOS', 'provincia_id' => $provincia->id],
            ['alianza' => 'LA']
        );

        $listaD = Lista::firstOrCreate(
            ['nombre' => 'Izquierda Unida', 'cargo' => 'DIPUTADOS', 'provincia_id' => $provincia->id],
            ['alianza' => null]
        );

        // ============ CREAR LISTAS PARA SENADORES ============
        $senadorA = Lista::firstOrCreate(
            ['nombre' => 'Frente de Todos', 'cargo' => 'SENADORES', 'provincia_id' => $provincia->id],
            ['alianza' => 'FDT']
        );

        $senadorB = Lista::firstOrCreate(
            ['nombre' => 'Juntos por el Cambio', 'cargo' => 'SENADORES', 'provincia_id' => $provincia->id],
            ['alianza' => 'JxC']
        );

        $senadorC = Lista::firstOrCreate(
            ['nombre' => 'Libertad Avanza', 'cargo' => 'SENADORES', 'provincia_id' => $provincia->id],
            ['alianza' => 'LA']
        );

        $senadorD = Lista::firstOrCreate(
            ['nombre' => 'Izquierda Unida', 'cargo' => 'SENADORES', 'provincia_id' => $provincia->id],
            ['alianza' => null]
        );

        // ============ TELEGRAMAS PARA DIPUTADOS ============
        // Mesa 1
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $listaA->id], [
            'votos_Diputados' => 120, 'votos_Senadores' => 0,
            'voto_Blancos' => 5, 'voto_Nulos' => 2, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $listaB->id], [
            'votos_Diputados' => 95, 'votos_Senadores' => 0,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $listaC->id], [
            'votos_Diputados' => 80, 'votos_Senadores' => 0,
            'voto_Blancos' => 2, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $listaD->id], [
            'votos_Diputados' => 30, 'votos_Senadores' => 0,
            'voto_Blancos' => 1, 'voto_Nulos' => 0, 'voto_Recurridos' => 0
        ]);

        // Mesa 2
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $listaA->id], [
            'votos_Diputados' => 140, 'votos_Senadores' => 0,
            'voto_Blancos' => 6, 'voto_Nulos' => 3, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $listaB->id], [
            'votos_Diputados' => 110, 'votos_Senadores' => 0,
            'voto_Blancos' => 4, 'voto_Nulos' => 2, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $listaC->id], [
            'votos_Diputados' => 90, 'votos_Senadores' => 0,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $listaD->id], [
            'votos_Diputados' => 35, 'votos_Senadores' => 0,
            'voto_Blancos' => 2, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);

        // Mesa 3
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $listaA->id], [
            'votos_Diputados' => 130, 'votos_Senadores' => 0,
            'voto_Blancos' => 5, 'voto_Nulos' => 2, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $listaB->id], [
            'votos_Diputados' => 105, 'votos_Senadores' => 0,
            'voto_Blancos' => 4, 'voto_Nulos' => 2, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $listaC->id], [
            'votos_Diputados' => 85, 'votos_Senadores' => 0,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $listaD->id], [
            'votos_Diputados' => 32, 'votos_Senadores' => 0,
            'voto_Blancos' => 1, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);

        // ============ TELEGRAMAS PARA SENADORES ============
        // Mesa 1
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $senadorA->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 115,
            'voto_Blancos' => 5, 'voto_Nulos' => 2, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $senadorB->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 90,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $senadorC->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 75,
            'voto_Blancos' => 2, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa1->id, 'lista_id' => $senadorD->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 28,
            'voto_Blancos' => 1, 'voto_Nulos' => 0, 'voto_Recurridos' => 0
        ]);

        // Mesa 2
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $senadorA->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 135,
            'voto_Blancos' => 6, 'voto_Nulos' => 3, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $senadorB->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 105,
            'voto_Blancos' => 4, 'voto_Nulos' => 2, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $senadorC->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 85,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa2->id, 'lista_id' => $senadorD->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 32,
            'voto_Blancos' => 2, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);

        // Mesa 3
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $senadorA->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 125,
            'voto_Blancos' => 5, 'voto_Nulos' => 2, 'voto_Recurridos' => 1
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $senadorB->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 100,
            'voto_Blancos' => 4, 'voto_Nulos' => 2, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $senadorC->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 80,
            'voto_Blancos' => 3, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);
        Telegrama::firstOrCreate(['mesa_id' => $mesa3->id, 'lista_id' => $senadorD->id], [
            'votos_Diputados' => 0, 'votos_Senadores' => 30,
            'voto_Blancos' => 1, 'voto_Nulos' => 1, 'voto_Recurridos' => 0
        ]);

        echo "✓ Telegramas creados con éxito\n";
        echo "  - Provincia: {$provincia->nombre}\n";
        echo "  - 3 mesas\n";
        echo "  - 4 listas para DIPUTADOS\n";
        echo "  - 4 listas para SENADORES\n";
        echo "  - Votos DIPUTADOS: FDT=390, JxC=310, LA=255, IU=97\n";
        echo "  - Votos SENADORES: FDT=375, JxC=295, LA=240, IU=90\n";
    }
}
