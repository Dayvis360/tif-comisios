<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lista;

class ListaController extends Controller
{
    public function index()
    {
        return response()->json(Lista::with('provincia')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string',
            'alianza' => 'nullable|string',
            'cargo' => 'required|string',
            'provincia_id' => 'required|exists:provincias,id'
        ]);

        $lista = Lista::create($validated);
        return response()->json($lista->load('provincia'), 201);
    }
}