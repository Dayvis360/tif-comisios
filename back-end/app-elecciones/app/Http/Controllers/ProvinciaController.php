<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProvinciaService;

//Controlador HTTP de provincias
class ProvinciaController extends Controller
{
    private ProvinciaService $provinciaService;

    public function __construct(ProvinciaService $provinciaService)
    {
        $this->provinciaService = $provinciaService;
    }

    //Listar todas las provincias
    public function index()
    {
        $provincias = $this->provinciaService->listarProvincias();
        return response()->json($provincias, 200);
    }

    //Obtener provincia por ID
    public function show($id)
    {
        $provincia = $this->provinciaService->obtenerProvincia($id);

        if (!$provincia) {
            return response()->json([
                'mensaje' => 'Provincia no encontrada'
            ], 404);
        }

        return response()->json($provincia, 200);
    }

    //Crear nueva provincia
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'bancas_diputados' => 'nullable|integer|min:1',
            'bancas_senadores' => 'nullable|integer|min:1'
        ]);

        try {
            $provincia = $this->provinciaService->registrarProvincia($validated);
            return response()->json($provincia, 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'mensaje' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al registrar la provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Actualizar provincia
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'bancas_diputados' => 'nullable|integer|min:1',
            'bancas_senadores' => 'nullable|integer|min:1'
        ]);

        try {
            $provincia = $this->provinciaService->actualizarProvincia($id, $validated);
            return response()->json($provincia, 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'mensaje' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar la provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Eliminar provincia
    public function destroy($id)
    {
        try {
            $resultado = $this->provinciaService->eliminarProvincia($id);
            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la provincia',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
