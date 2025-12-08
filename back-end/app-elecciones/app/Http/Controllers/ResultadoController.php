<?php

namespace App\Http\Controllers;

use App\Services\ResultadoService;
use Illuminate\Http\Request;

//Controlador HTTP de resultados electorales
class ResultadoController extends Controller
{
    private ResultadoService $resultadoService;

    public function __construct(ResultadoService $resultadoService)
    {
        $this->resultadoService = $resultadoService;
    }

    //Obtener resultados nacionales completos
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

    //Obtener ranking nacional por cargo
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

    //Obtener estadísticas completas de una provincia
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

    //Obtener resumen de todas las provincias
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

    //Exportar resultados de una provincia en CSV o JSON
    public function exportarProvincia(int $id, Request $request)
    {
        try {
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

    //Exportar resultados nacionales en CSV o JSON
    public function exportarNacional(Request $request)
    {
        try {
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

    //Exportar resumen de todas las provincias en CSV o JSON
    public function exportarResumenProvincias(Request $request)
    {
        try {
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
