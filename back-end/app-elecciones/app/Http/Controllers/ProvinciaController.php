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
}
