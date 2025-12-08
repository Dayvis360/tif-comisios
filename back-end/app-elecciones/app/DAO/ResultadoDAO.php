<?php

namespace App\DAO;

use App\Models\Lista;
use App\Models\Mesa;
use App\Models\Provincia;
use App\Models\Telegrama;
use Illuminate\Support\Facades\DB;

class ResultadoDAO
{
    //Obtener totales nacionales por lista y cargo
    public function getTotalesNacionales(): array
    {
        return Lista::select(
                'listas.id as lista_id',
                'listas.nombre as lista_nombre',
                'listas.cargo',
                DB::raw('SUM(telegramas.votos_Diputados) as total_votos_diputados'),
                DB::raw('SUM(telegramas.votos_Senadores) as total_votos_senadores'),
                DB::raw('COUNT(DISTINCT telegramas.mesa_id) as total_mesas')
            )
            ->leftJoin('telegramas', 'listas.id', '=', 'telegramas.lista_id')
            ->groupBy('listas.id', 'listas.nombre', 'listas.cargo')
            ->orderBy('listas.cargo')
            ->orderByDesc('total_votos_diputados')
            ->get()
            ->map(fn($item) => (object)$item->toArray())
            ->toArray();
    }

    //Obtener participación electoral nacional
    public function getParticipacionNacional(): object
    {
        $result = Mesa::select(
                DB::raw('SUM(mesas.electores) as total_electores'),
                DB::raw('SUM(telegramas.votos_Diputados + telegramas.votos_Senadores + telegramas.voto_Blancos + telegramas.voto_Nulos + telegramas.voto_Recurridos) as total_votos_emitidos'),
                DB::raw('COUNT(DISTINCT mesas.id) as total_mesas')
            )
            ->leftJoin('telegramas', 'mesas.id', '=', 'telegramas.mesa_id')
            ->first();

        return $result ?: (object)[
            'total_electores' => 0,
            'total_votos_emitidos' => 0,
            'total_mesas' => 0
        ];
    }

    //Obtener totales por provincia
    public function getTotalesPorProvincia(int $provinciaId): array
    {
        return Lista::select(
                'listas.id as lista_id',
                'listas.nombre as lista_nombre',
                'listas.cargo',
                DB::raw('SUM(telegramas.votos_Diputados) as total_votos_diputados'),
                DB::raw('SUM(telegramas.votos_Senadores) as total_votos_senadores'),
                DB::raw('SUM(telegramas.voto_Blancos) as total_blancos'),
                DB::raw('SUM(telegramas.voto_Nulos) as total_nulos'),
                DB::raw('SUM(telegramas.voto_Recurridos) as total_recurridos'),
                DB::raw('COUNT(telegramas.id) as total_telegramas')
            )
            ->leftJoin('telegramas', 'listas.id', '=', 'telegramas.lista_id')
            ->leftJoin('mesas', 'telegramas.mesa_id', '=', 'mesas.id')
            ->where('mesas.provincia_id', $provinciaId)
            ->groupBy('listas.id', 'listas.nombre', 'listas.cargo')
            ->orderBy('listas.cargo')
            ->orderByDesc('total_votos_diputados')
            ->get()
            ->map(fn($item) => (object)$item->toArray())
            ->toArray();
    }

    //Obtener participación por provincia
    public function getParticipacionPorProvincia(int $provinciaId): object
    {
        $result = Provincia::select(
                'provincias.id as provincia_id',
                'provincias.nombre as provincia_nombre',
                DB::raw('SUM(mesas.electores) as total_electores'),
                DB::raw('COUNT(DISTINCT mesas.id) as total_mesas'),
                DB::raw('COUNT(DISTINCT telegramas.id) as total_telegramas'),
                DB::raw('SUM(telegramas.votos_Diputados) as total_votos_diputados'),
                DB::raw('SUM(telegramas.votos_Senadores) as total_votos_senadores'),
                DB::raw('SUM(telegramas.voto_Blancos) as total_blancos'),
                DB::raw('SUM(telegramas.voto_Nulos) as total_nulos'),
                DB::raw('SUM(telegramas.voto_Recurridos) as total_recurridos')
            )
            ->leftJoin('mesas', 'provincias.id', '=', 'mesas.provincia_id')
            ->leftJoin('telegramas', 'mesas.id', '=', 'telegramas.mesa_id')
            ->where('provincias.id', $provinciaId)
            ->groupBy('provincias.id', 'provincias.nombre')
            ->first();

        return $result ?: (object)[
            'provincia_id' => $provinciaId,
            'provincia_nombre' => '',
            'total_electores' => 0,
            'total_mesas' => 0,
            'total_telegramas' => 0,
            'total_votos_diputados' => 0,
            'total_votos_senadores' => 0,
            'total_blancos' => 0,
            'total_nulos' => 0,
            'total_recurridos' => 0
        ];
    }

    //Obtener ranking nacional de listas por cargo
    public function getRankingNacional(string $cargo): array
    {
        return Lista::select(
                'listas.id as lista_id',
                'listas.nombre as lista_nombre',
                'listas.cargo',
                DB::raw('SUM(CASE WHEN listas.cargo = "DIPUTADOS" THEN telegramas.votos_Diputados ELSE 0 END) as total_votos_diputados'),
                DB::raw('SUM(CASE WHEN listas.cargo = "SENADORES" THEN telegramas.votos_Senadores ELSE 0 END) as total_votos_senadores'),
                DB::raw('COUNT(DISTINCT telegramas.mesa_id) as mesas_con_votos')
            )
            ->leftJoin('telegramas', 'listas.id', '=', 'telegramas.lista_id')
            ->where('listas.cargo', $cargo)
            ->groupBy('listas.id', 'listas.nombre', 'listas.cargo')
            ->orderByRaw('CASE 
                WHEN listas.cargo = "DIPUTADOS" THEN SUM(telegramas.votos_Diputados)
                ELSE SUM(telegramas.votos_Senadores)
            END DESC')
            ->get()
            ->map(fn($item) => (object)$item->toArray())
            ->toArray();
    }

    //Obtener votos válidos vs inválidos por provincia
    public function getVotosValidosInvalidos(int $provinciaId): object
    {
        $result = Telegrama::select(
                DB::raw('SUM(telegramas.votos_Diputados + telegramas.votos_Senadores) as votos_validos'),
                DB::raw('SUM(telegramas.voto_Blancos) as votos_blancos'),
                DB::raw('SUM(telegramas.voto_Nulos) as votos_nulos'),
                DB::raw('SUM(telegramas.voto_Recurridos) as votos_recurridos')
            )
            ->join('mesas', 'telegramas.mesa_id', '=', 'mesas.id')
            ->where('mesas.provincia_id', $provinciaId)
            ->first();

        return $result ?: (object)[
            'votos_validos' => 0,
            'votos_blancos' => 0,
            'votos_nulos' => 0,
            'votos_recurridos' => 0
        ];
    }

    //Obtener resumen de todas las provincias
    public function getResumenTodasProvincias(): array
    {
        return Provincia::select(
                'provincias.id as provincia_id',
                'provincias.nombre as provincia_nombre',
                'provincias.bancas_diputados',
                'provincias.bancas_senadores',
                DB::raw('COUNT(DISTINCT mesas.id) as total_mesas'),
                DB::raw('SUM(mesas.electores) as total_electores'),
                DB::raw('COUNT(DISTINCT telegramas.id) as total_telegramas'),
                DB::raw('SUM(telegramas.votos_Diputados) as total_votos_diputados'),
                DB::raw('SUM(telegramas.votos_Senadores) as total_votos_senadores')
            )
            ->leftJoin('mesas', 'provincias.id', '=', 'mesas.provincia_id')
            ->leftJoin('telegramas', 'mesas.id', '=', 'telegramas.mesa_id')
            ->groupBy('provincias.id', 'provincias.nombre', 'provincias.bancas_diputados', 'provincias.bancas_senadores')
            ->orderBy('provincias.nombre')
            ->get()
            ->map(fn($item) => (object)$item->toArray())
            ->toArray();
    }
}
