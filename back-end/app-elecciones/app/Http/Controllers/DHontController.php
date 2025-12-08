<?php

namespace App\Http\Controllers;

use App\Services\DHontService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//Controlador HTTP para cálculo de reparto de bancas con método D'Hont
class DHontController extends Controller
{
    private DHontService $dhontService;

    public function __construct(DHontService $dhontService)
    {
        $this->dhontService = $dhontService;
    }

    //Calcular reparto de bancas para una provincia
    public function calcularPorProvincia(int $provinciaId, Request $request): JsonResponse
    {
        try {
            $cargo = $request->query('cargo', 'DIPUTADOS');
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

    //Calcular reparto de bancas para todas las provincias
    public function calcularTodasProvincias(Request $request): JsonResponse
    {
        try {
            $cargo = $request->query('cargo', 'DIPUTADOS');
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

    //Obtener resumen de resultados electorales para diputados y senadores
    public function obtenerResumen(): JsonResponse
    {
        try {
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
