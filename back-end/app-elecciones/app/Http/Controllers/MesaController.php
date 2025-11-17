<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mesa;

class MesaController extends Controller
{
    public function index()
    {
        return response()->json(Mesa::with('provincia')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'provincia_id' => 'required|exists:provincias,id',
            'circuito' => 'required|string',
            'establecimiento' => 'required|string',
            'electores' => 'required|integer|min:1'
        ]);

        $mesa = Mesa::create($validated);
        return response()->json($mesa->load('provincia'), 201);
    }
    public function show ($id)
    {

    }
    public function update (Request $request, $id)
    {
        $validated = $request->validate([
            'provincia_id' => 'required|exists:provincias,id',
            'circuito' => 'required|string',
            'establecimiento' => 'required|string',
            'electores' => 'required|integer|min:1'
        ]);

        $mesa = Mesa::findOrFail($id);
        $mesa->update($validated);
        return response()->json($mesa->load('provincia'), 200);
    }
    public function destroy($id)
    {
        $mesa = Mesa::findOrFail($id);
        $mesa->delete();
        return response()->json(null, 204);
    }
}