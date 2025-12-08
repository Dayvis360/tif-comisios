<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ListaService;

/**
 * ListaController
 * 
 * Responsabilidad:
 * - Recibir peticiones HTTP
 * - Validar datos de entrada
 * - Llamar al Service
 * - Devolver respuestas HTTP
 */
class ListaController extends Controller
{
    private ListaService $listaService;

    public function __construct(ListaService $listaService)
    {
        $this->listaService = $listaService;
    }

    /**
     * GET /api/listas
     */
    public function index()
    {
        $listas = $this->listaService->listarListas();
        return response()->json($listas, 200);
    }

    /**
     * GET /api/listas/{id}
     */
    public function show(Request $request, $id)
    {
        $lista = $this->listaService->obtenerLista($id);

        if (!$lista) {
            return response()->json(['mensaje' => 'Lista no encontrada'], 404);
        }

        return response()->json($lista, 200);
    }

    /**
     * POST /api/listas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'alianza' => 'nullable|string|max:255',
            'cargo' => 'required|string|in:DIPUTADOS,SENADORES,diputados,senadores',
            'provincia_id' => 'required|exists:provincias,id'
        ]);

        try {
            $lista = $this->listaService->registrarLista($validated);
            return response()->json($lista, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al registrar la lista', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT/PATCH /api/listas/{id}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'alianza' => 'nullable|string|max:255',
            'cargo' => 'required|string|in:DIPUTADOS,SENADORES,diputados,senadores',
            'provincia_id' => 'required|exists:provincias,id'
        ]);

        try {
            $lista = $this->listaService->actualizarLista($id, $validated);
            return response()->json($lista, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al actualizar la lista', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/listas/{id}
     */
    public function destroy($id)
    {
        try {
            $resultado = $this->listaService->eliminarLista($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al eliminar la lista', 'error' => $e->getMessage()], 400);
        }
    }
}