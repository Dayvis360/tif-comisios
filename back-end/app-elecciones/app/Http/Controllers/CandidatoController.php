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
}