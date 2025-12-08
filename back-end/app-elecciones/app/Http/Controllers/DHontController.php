<?php

namespace App\Http\Controllers;

use App\Services\DHontService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DHontController - Controlador para cálculo de reparto de bancas
 * 
 * Responsabilidad:
 * - Manejar peticiones HTTP relacionadas con el método D'Hont
 * - Validar parámetros de entrada
 * - Delegar cálculo al DHontService
 * - Retornar respuestas JSON
 */
class DHontController extends Controller
{
    private DHontService $dhontService;

    public function __construct(DHontService $dhontService)
    {
        $this->dhontService = $dhontService;
    }

    /**
     * Calcular reparto de bancas para una provincia específica
     * 
     * GET /api/dhont/provincia/{provinciaId}?cargo=DIPUTADOS
     * 
     * @param int $provinciaId
     * @param Request $request
     * @return JsonResponse
     */
    public function calcularPorProvincia(int $provinciaId, Request $request): JsonResponse
    {
        try {
            // Obtener cargo del query string (por defecto DIPUTADOS)
            $cargo = $request->query('cargo', 'DIPUTADOS');

            // Calcular reparto
            $resultado = $this->dhontService->calcularRepartoBancas($provinciaId, $cargo);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcular reparto de bancas para todas las provincias
     * 
     * GET /api/dhont/todas?cargo=DIPUTADOS
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calcularTodasProvincias(Request $request): JsonResponse
    {
        try {
            // Obtener cargo del query string (por defecto DIPUTADOS)
            $cargo = $request->query('cargo', 'DIPUTADOS');

            // Calcular reparto para todas las provincias
            $resultado = $this->dhontService->calcularRepartoTodasProvincias($cargo);

            return response()->json([
                'success' => true,
                'data' => $resultado,
            ], 200);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener resumen de resultados electorales
     * 
     * GET /api/dhont/resumen
     * 
     * @return JsonResponse
     */
    public function obtenerResumen(): JsonResponse
    {
        try {
            // Calcular para diputados y senadores
            $diputados = $this->dhontService->calcularRepartoTodasProvincias('DIPUTADOS');
            $senadores = $this->dhontService->calcularRepartoTodasProvincias('SENADORES');

            return response()->json([
                'success' => true,
                'data' => [
                    'diputados' => $diputados,
                    'senadores' => $senadores,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
