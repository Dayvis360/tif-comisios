<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProvinciaService;

/**
 * ProvinciaController
 * 
 * Responsabilidad principal:
 * - Recibir la petición HTTP del cliente
 * - Validar datos básicos de la request (campos obligatorios, formato)
 * - Llamar al Service para que ejecute el caso de uso
 * - Devolver una respuesta HTTP (código de estado y JSON)
 * 
 * NO contiene lógica de negocio. Solo recibe la petición, valida y delega al Service.
 */
class ProvinciaController extends Controller
{
    private ProvinciaService $provinciaService;

    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(ProvinciaService $provinciaService)
    {
        $this->provinciaService = $provinciaService;
    }

    /**
     * GET /api/provincias
     * Listar todas las provincias
     */
    public function index()
    {
        $provincias = $this->provinciaService->listarProvincias();
        return response()->json($provincias, 200);
    }

    /**
     * GET /api/provincias/{id}
     * Obtener una provincia específica
     */
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

    /**
     * POST /api/provincias
     * Crear una nueva provincia
     * 
     * Body (JSON):
     * {
     *   "nombre": "Buenos Aires",
     *   "bancas_diputados": 35,
     *   "bancas_senadores": 3
     * }
     */
    public function store(Request $request)
    {
        // 1. Validar datos de entrada (responsabilidad del Controller)
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'bancas_diputados' => 'nullable|integer|min:1',
            'bancas_senadores' => 'nullable|integer|min:1'
        ]);

        try {
            // 2. Delegar al Service para ejecutar el caso de uso
            $provincia = $this->provinciaService->registrarProvincia($validated);

            // 3. Devolver respuesta HTTP exitosa
            return response()->json($provincia, 201);

        } catch (\InvalidArgumentException $e) {
            // Manejar errores de validación de negocio
            return response()->json([
                'mensaje' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            // Manejar otros errores
            return response()->json([
                'mensaje' => 'Error al registrar la provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT/PATCH /api/provincias/{id}
     * Actualizar una provincia existente
     * 
     * Body (JSON):
     * {
     *   "nombre": "Buenos Aires",
     *   "bancas_diputados": 40,
     *   "bancas_senadores": 3
     * }
     */
    public function update(Request $request, $id)
    {
        // 1. Validar datos de entrada
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'bancas_diputados' => 'nullable|integer|min:1',
            'bancas_senadores' => 'nullable|integer|min:1'
        ]);

        try {
            // 2. Delegar al Service
            $provincia = $this->provinciaService->actualizarProvincia($id, $validated);

            // 3. Devolver respuesta HTTP exitosa
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

    /**
     * DELETE /api/provincias/{id}
     * Eliminar una provincia
     */
    public function destroy($id)
    {
        try {
            // Delegar al Service
            $resultado = $this->provinciaService->eliminarProvincia($id);

            // Devolver respuesta HTTP exitosa (204 No Content o 200 con mensaje)
            return response()->json($resultado, 200);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar la provincia',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
