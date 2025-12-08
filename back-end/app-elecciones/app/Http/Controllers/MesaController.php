<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MesaService;

/**
 * MesaController
 * 
 * Responsabilidad:
 * - Recibir peticiones HTTP
 * - Validar datos de entrada
 * - Llamar al Service
 * - Devolver respuestas HTTP
 */
class MesaController extends Controller
{
    private MesaService $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    /**
     * GET /api/mesas
     */
    public function index()
    {
        $mesas = $this->mesaService->listarMesas();
        return response()->json($mesas, 200);
    }

    /**
     * GET /api/mesas/{id}
     */
    public function show($id)
    {
        $mesa = $this->mesaService->obtenerMesa($id);

        if (!$mesa) {
            return response()->json(['mensaje' => 'Mesa no encontrada'], 404);
        }

        return response()->json($mesa, 200);
    }

    /**
     * POST /api/mesas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'provincia_id' => 'required|exists:provincias,id',
            'circuito' => 'required|string|max:255',
            'establecimiento' => 'required|string|max:255',
            'electores' => 'required|integer|min:1'
        ]);

        try {
            $mesa = $this->mesaService->registrarMesa($validated);
            return response()->json($mesa, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al registrar la mesa', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT/PATCH /api/mesas/{id}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'provincia_id' => 'required|exists:provincias,id',
            'circuito' => 'required|string|max:255',
            'establecimiento' => 'required|string|max:255',
            'electores' => 'required|integer|min:1'
        ]);

        try {
            $mesa = $this->mesaService->actualizarMesa($id, $validated);
            return response()->json($mesa, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al actualizar la mesa', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/mesas/{id}
     */
    public function destroy($id)
    {
        try {
            $resultado = $this->mesaService->eliminarMesa($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al eliminar la mesa', 'error' => $e->getMessage()], 400);
        }
    }
}