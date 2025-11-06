<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provincia;

class ProvinciaController extends Controller
{
    //
    public function index()
    {
        return response()->json(Provincia::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|unique:provincias,nombre'
        ]);

        $provincia = Provincia::create($validated);
        return response()->json($provincia, 201);
    }
    public function show($id)
    {
        
    }
    
    public function update (Request $request,  $id){
        $validated = $request->validate([
            'nombre' => 'required|string|unique:provincias,nombre,'.$id
        ]);

        $provincia = Provincia::findOrFail($id);
        $provincia->update($validated);
        return response()->json($provincia, 200);
    }

    public function destroy($id)
    {
        $provincia = Provincia::findOrFail();
        $provincia->delete();
        return response()->json(null, 204);
    }
    
}
