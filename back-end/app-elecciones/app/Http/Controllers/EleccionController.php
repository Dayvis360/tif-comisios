<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Lista;
use App\Models\Mesa;
use App\Models\Provincia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EleccionController extends Controller
{
    /**
     * Crear un candidato, mesa y provincia con los datos proporcionados
     */
    public function crearRegistros(Request $request): JsonResponse
    {
        // Validar los datos de entrada
        $request->validate([
            'provincia' => 'required|string|max:255',
            'mesa' => 'required|array',
            'mesa.circuito' => 'required|string|max:255',
            'mesa.establecimiento' => 'required|string|max:255',
            'mesa.electores' => 'required|integer|min:1',
            'lista' => 'required|array',
            'lista.cargo' => 'required|string|max:255',
            'lista.nombre_lista' => 'required|string|max:255',
            'lista.alianza' => 'required|string|max:255',
            'candidato' => 'required|array',
            'candidato.nombre' => 'required|string|max:255',
            'candidato.orden_en_lista' => 'required|integer|min:1',
        ]);

        try {
            // provincia name  == nameDB = nameDB o name =/ nameDB = name
            $provincia = Provincia::firstOrCreate(
                ['nombre' => $request->provincia],
                ['nombre' => $request->provincia]
            );

            // Crear la mesa
            $mesa = Mesa::create([
                'provincia_id' => $provincia->id,
                'circuito' => $request->mesa['circuito'],
                'establecimiento' => $request->mesa['establecimiento'],
                'electores' => $request->mesa['electores'],
            ]);

            // Crear la lista
            $lista = Lista::create([
                'provincia_id' => $provincia->id,
                'cargo' => $request->lista['cargo'],
                'nombre_lista' => $request->lista['nombre_lista'],
                'alianza' => $request->lista['alianza'],
            ]);

            // Crear el candidato
            $candidato = Candidato::create([
                'lista_id' => $lista->id,
                'nombre' => $request->candidato['nombre'],
                'orden_en_lista' => $request->candidato['orden_en_lista'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registros creados exitosamente',
                'data' => [
                    'provincia' => $provincia,
                    'mesa' => $mesa,
                    'lista' => $lista,
                    'candidato' => $candidato,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear los registros: ' . $e->getMessage()
            ], 500);
        }
    }
}
