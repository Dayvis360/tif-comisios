<?php

namespace App\Http\Controllers;

use app\Models\Telegrama;
use app\Models\Candidato;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotoController extends Controller
{
    public function votosPorNombre($nombre)
    {
        // Buscar el candidato por nombre
        $candidato = Candidato::where('nombre', $nombre)->first();

        // Verificar si el candidato existe
        if (!$candidato) {
            return response()->json(['error' => 'Candidato no encontrado'], 404);
        }

        // Verificar si el candidato tiene una lista asociada
        $lista = $candidato->lista;
        if (!$lista) {
            return response()->json(['error' => 'Lista no encontrada para el candidato'], 404);
        }

        // Buscar los telegramas asociados a la lista
        $res = Telegrama::where('lista_id', $lista->id)->get();

        // Devolver la respuesta en formato JSON
        return response()->json($res);
    }
}