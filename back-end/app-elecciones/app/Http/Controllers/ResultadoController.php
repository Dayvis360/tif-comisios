<?php

namespace App\Http\Controllers;

use App\Services\ResultadoService;
use Illuminate\Http\Request;

/**
 * ResultadoController
 * 
 * Responsabilidad:
 * - Recibir peticiones HTTP para resultados electorales
 * - Validar parámetros
 * - Llamar al Service
 * - Devolver respuestas JSON
 */
class ResultadoController extends Controller
{
    private ResultadoService $resultadoService;

    public function __construct(ResultadoService $resultadoService)
    {
        $this->resultadoService = $resultadoService;
    }

    /**
     * GET /api/resultados/nacional
     * 
     * Obtener resultados nacionales completos:
     * - Participación electoral
     * - Totales por lista (diputados y senadores)
     * - Porcentajes
     */
    public function nacional()
    {
        try {
            $resultados = $this->resultadoService->obtenerResultadosNacionales();
            return response()->json($resultados, 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener resultados nacionales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/ranking/{cargo}
     * 
     * Obtener ranking nacional por cargo (DIPUTADOS o SENADORES)
     */
    public function ranking(string $cargo)
    {
        try {
            $cargo = strtoupper($cargo);
            $ranking = $this->resultadoService->obtenerRankingNacional($cargo);
            return response()->json($ranking, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'mensaje' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener ranking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/provincia/{id}/estadisticas
     * 
     * Obtener estadísticas completas de una provincia:
     * - Participación
     * - Votos válidos vs inválidos
     * - Totales por lista
     */
    public function estadisticasProvincia(int $id)
    {
        try {
            $estadisticas = $this->resultadoService->obtenerEstadisticasProvincia($id);
            return response()->json($estadisticas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener estadísticas de la provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/provincias
     * 
     * Obtener resumen de todas las provincias
     */
    public function resumenProvincias()
    {
        try {
            $resumen = $this->resultadoService->obtenerResumenProvincias();
            return response()->json($resumen, 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al obtener resumen de provincias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/provincia/{id}/exportar?formato=csv
     * GET /api/resultados/provincia/{id}/exportar?formato=json
     * 
     * Exportar resultados de una provincia en CSV o JSON
     */
    public function exportarProvincia(int $id, Request $request)
    {
        try {
            // Validar formato
            $validated = $request->validate([
                'formato' => 'required|string|in:csv,json'
            ]);

            $formato = $validated['formato'];

            if ($formato === 'csv') {
                $csv = $this->resultadoService->exportarResultadosProvinciaCSV($id);
                
                return response($csv, 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="resultados_provincia_' . $id . '.csv"');
            } else {
                $json = $this->resultadoService->exportarResultadosProvinciaJSON($id);
                
                return response($json, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('Content-Disposition', 'attachment; filename="resultados_provincia_' . $id . '.json"');
            }

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al exportar resultados de la provincia',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/nacional/exportar?formato=csv
     * GET /api/resultados/nacional/exportar?formato=json
     * 
     * Exportar resultados nacionales en CSV o JSON
     */
    public function exportarNacional(Request $request)
    {
        try {
            // Validar formato
            $validated = $request->validate([
                'formato' => 'required|string|in:csv,json'
            ]);

            $formato = $validated['formato'];

            if ($formato === 'csv') {
                $csv = $this->resultadoService->exportarResultadosNacionalesCSV();
                
                return response($csv, 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="resultados_nacionales.csv"');
            } else {
                $json = $this->resultadoService->exportarResultadosNacionalesJSON();
                
                return response($json, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('Content-Disposition', 'attachment; filename="resultados_nacionales.json"');
            }

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al exportar resultados nacionales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/resultados/provincias/exportar?formato=csv
     * GET /api/resultados/provincias/exportar?formato=json
     * 
     * Exportar resumen de todas las provincias en CSV o JSON
     */
    public function exportarResumenProvincias(Request $request)
    {
        try {
            // Validar formato
            $validated = $request->validate([
                'formato' => 'required|string|in:csv,json'
            ]);

            $formato = $validated['formato'];

            if ($formato === 'csv') {
                $csv = $this->resultadoService->exportarResumenProvinciasCSV();
                
                return response($csv, 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="resumen_provincias.csv"');
            } else {
                $json = $this->resultadoService->exportarResumenProvinciasJSON();
                
                return response($json, 200)
                    ->header('Content-Type', 'application/json')
                    ->header('Content-Disposition', 'attachment; filename="resumen_provincias.json"');
            }

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al exportar resumen de provincias',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
