<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidato;

class CandidatoController extends Controller
{   
    public function index()
    {
        return response()->json(Candidato::with('lista.provincia')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'orden_en_lista' => 'required|integer|min:1',
            'lista_id' => 'required|exists:listas,id'
        ]);

        $candidato = Candidato::create($validated);
        return response()->json($candidato->load('lista.provincia'), 201);
    }
    public function show(Request $request, $id)
    {

    }
    public function update (Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'orden_en_lista' => 'required|integer|min:1',
            'lista_id' => 'required|exists:listas,id'
        ]);

        $candidato = Candidato::findOrFail($id);
        $candidato->update($validated);
        return response()->json($candidato->load('lista.provincia'), 200);
    }
    public function destroy($id)
    {
        $candidato = Candidato::findOrFail($id);
        $candidato->delete();
        return response()->json(null, 204);
    }

public function resultados($id) 
{
    $candidato = Candidato::with('lista.provincia')->findOrFail($id);

    $cargo = $candidato->lista->cargo ?? 'DIPUTADOS';




$totalLista = \DB::table('telegramas')
        ->where('provincia_id', $candidato->lista->provincia->id)

        ->where('lista_id', $candidato->lista->id)

        ->sum($cargo === 'DIPUTADOS' ? 'votos_diputados' : 'votos_senadores');

    $totalValidos = \DB::table('telegramas')
        ->where('provincia_id', $candidato->lista->provincia->id)
        
        ->sum($cargo === 'DIPUTADOS' ? 'votos_diputados' : 'votos_senadores');

    $porcentaje = $totalValidos > 0 ? round(($totalLista / $totalValidos) * 100, 2) : 0;

    return response()->json([
        'candidato' => $candidato->nombre,
        'lista' => $candidato->lista->nombre,
        'provincia' => $candidato->lista->provincia->nombre,
        'cargo' => $cargo,
        'votos_lista' => $totalLista,
        'porcentaje_en_provincia' => $porcentaje . '%'
    ]);
}
}