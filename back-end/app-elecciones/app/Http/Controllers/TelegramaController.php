<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Telegrama;

class TelegramaController extends Controller
{
    public function index()
    {
        return response()->json(Telegrama::with(['mesa.provincia', 'lista'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'lista_id' => 'required|exists:listas,id',
            'votos_Diputados' => 'required|integer|min:0',
            'votos_Senadores' => 'required|integer|min:0',
            'voto_Blancos' => 'required|integer|min:0',
            'voto_Nulos' => 'required|integer|min:0',
            'voto_Recurridos' => 'required|integer|min:0'
        ]);

        $telegrama = Telegrama::create($validated);
        return response()->json($telegrama->load(['mesa.provincia', 'lista']), 201);
    }
    public function show(Request $request, $id)
    {

    }
    public function update (Request $request, $id)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'lista_id' => 'required|exists:listas,id',
            'votos_Diputados' => 'required|integer|min:0',
            'votos_Senadores' => 'required|integer|min:0',
            'voto_Blancos' => 'required|integer|min:0',
            'voto_Nulos' => 'required|integer|min:0',
            'voto_Recurridos' => 'required|integer|min:0'
        ]);

        $telegrama = Telegrama::findOrFail($id);
        $telegrama->update($validated);
        return response()->json($telegrama->load(['mesa.provincia', 'lista']), 200);
    }
    public function destroy($id)
    {
        $telegrama = Telegrama::findOrFail($id);
        $telegrama->delete();
        return response()->json(null, 204);
    }
}