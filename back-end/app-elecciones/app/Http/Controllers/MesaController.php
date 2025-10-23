<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lista;
use App\Models\Mesa;
use App\Models\Provincia;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Cast\Void_;

class ListaController extends Controller
{
    //
    public function craerMesa(Request $request) 
    {
         $data = $request->validate([
            'provincia' => 'required|string',
            'circuito' => 'required|string',
            'establecimiento' => 'required|string',
            'electores' => 'required|integer|min:1'
        ]);

        $provincia = Provincia::firstOrCreate(
            ['nombre' => $data['provincia']]
        );

        $Mesa = Mesa::create([
            'provincia_id' => $provincia->id,
            'circuito' => $data['circuito'],
            'establecimiento' => $data['establecimiento'],
            'electores' => $data['electores']
        ]);
        return response()->json(['message' => 'Mesa creada con éxito', 'mesa_id' => $Mesa->id], 201);
    }
}
