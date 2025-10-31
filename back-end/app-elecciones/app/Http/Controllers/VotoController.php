<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotoController extends Controller
{
    public function resultados()
    {
        // Obtener votos totales por cargo
        $votosPorCargo = DB::table('votos')
            ->select('cargo', DB::raw('SUM(cantidad_votos) as total_votos'))
            ->groupBy('cargo')
            ->get();

        // Obtener porcentaje de cada lista por cargo
        $resultados = DB::table('votos')
            ->join('listas', 'votos.lista_id', '=', 'listas.id')
            ->select(
                'votos.cargo',
                'listas.nombre as lista',
                DB::raw('SUM(votos.cantidad_votos) as votos'),
                DB::raw('ROUND(100 * SUM(votos.cantidad_votos) / 
                    (SELECT SUM(v2.cantidad_votos) FROM votos v2 WHERE v2.cargo = votos.cargo), 2) as porcentaje')
            )
            ->groupBy('votos.cargo', 'listas.nombre')
            ->orderBy('votos.cargo')
            ->orderByDesc('votos')
            ->get();

        return view('resultados', compact('resultados'));
    }
}