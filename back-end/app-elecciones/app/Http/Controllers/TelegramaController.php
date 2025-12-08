<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramaService;

/**
 * TelegramaController
 * 
 * Responsabilidad:
 * - Recibir peticiones HTTP
 * - Validar datos de entrada
 * - Llamar al Service
 * - Devolver respuestas HTTP
 */
class TelegramaController extends Controller
{
    private TelegramaService $telegramaService;

    public function __construct(TelegramaService $telegramaService)
    {
        $this->telegramaService = $telegramaService;
    }

    /**
     * GET /api/telegramas
     * Permite filtrar por: provincia_id, cargo, lista_id, mesa_desde, mesa_hasta
     */
    public function index(Request $request)
    {
        // Si no hay filtros, devolver todos
        if (!$request->hasAny(['provincia_id', 'cargo', 'lista_id', 'mesa_desde', 'mesa_hasta'])) {
            $telegramas = $this->telegramaService->listarTelegramas();
            return response()->json($telegramas, 200);
        }

        // Validar filtros
        $validated = $request->validate([
            'provincia_id' => 'nullable|integer|exists:provincias,id',
            'cargo' => 'nullable|string|in:DIPUTADOS,SENADORES',
            'lista_id' => 'nullable|integer|exists:listas,id',
            'mesa_desde' => 'nullable|integer|exists:mesas,id',
            'mesa_hasta' => 'nullable|integer|exists:mesas,id'
        ]);

        // Listar con filtros
        $telegramas = $this->telegramaService->listarTelegramasConFiltros(
            $validated['provincia_id'] ?? null,
            $validated['cargo'] ?? null,
            $validated['lista_id'] ?? null,
            $validated['mesa_desde'] ?? null,
            $validated['mesa_hasta'] ?? null
        );

        return response()->json($telegramas, 200);
    }

    /**
     * GET /api/telegramas/{id}
     */
    public function show(Request $request, $id)
    {
        $telegrama = $this->telegramaService->obtenerTelegrama($id);

        if (!$telegrama) {
            return response()->json(['mensaje' => 'Telegrama no encontrado'], 404);
        }

        return response()->json($telegrama, 200);
    }

    /**
     * POST /api/telegramas
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'lista_id' => 'required|exists:listas,id',
            'votos_Diputados' => 'required|integer|min:0',
            'votos_Senadores' => 'required|integer|min:0',
            'voto_Blancos' => 'required|integer|min:0',
            'voto_Nulos' => 'required|integer|min:0',
            'voto_Recurridos' => 'required|integer|min:0',
            'usuario' => 'nullable|string|max:100'
        ]);

        try {
            $telegrama = $this->telegramaService->registrarTelegrama($validated);
            return response()->json($telegrama, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al registrar el telegrama', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT/PATCH /api/telegramas/{id}
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'lista_id' => 'required|exists:listas,id',
            'votos_Diputados' => 'required|integer|min:0',
            'votos_Senadores' => 'required|integer|min:0',
            'voto_Blancos' => 'required|integer|min:0',
            'voto_Nulos' => 'required|integer|min:0',
            'voto_Recurridos' => 'required|integer|min:0',
            'usuario' => 'nullable|string|max:100'
        ]);

        try {
            $telegrama = $this->telegramaService->actualizarTelegrama($id, $validated);
            return response()->json($telegrama, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al actualizar el telegrama', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/telegramas/{id}
     */
    public function destroy($id)
    {
        try {
            $resultado = $this->telegramaService->eliminarTelegrama($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al eliminar el telegrama', 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/telegramas/importar
     * Body: archivo CSV o JSON con telegramas
     */
    public function importar(Request $request)
    {
        // Validar que se envíe un archivo o datos JSON
        $validated = $request->validate([
            'archivo' => 'nullable|file|mimes:csv,txt,json|max:10240', // 10MB max
            'datos' => 'nullable|array',
            'datos.*.mesa_id' => 'required|integer',
            'datos.*.lista_id' => 'required|integer',
            'datos.*.votos_Diputados' => 'required|integer|min:0',
            'datos.*.votos_Senadores' => 'required|integer|min:0',
            'datos.*.voto_Blancos' => 'required|integer|min:0',
            'datos.*.voto_Nulos' => 'required|integer|min:0',
            'datos.*.voto_Recurridos' => 'required|integer|min:0'
        ]);

        try {
            $datos = [];
            $formato = 'json';

            // Caso 1: Archivo CSV
            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $extension = $archivo->getClientOriginalExtension();

                if ($extension === 'csv' || $extension === 'txt') {
                    $formato = 'csv';
                    $datos = $this->parsearCSV($archivo->getRealPath());
                } elseif ($extension === 'json') {
                    $formato = 'json';
                    $contenido = file_get_contents($archivo->getRealPath());
                    $datos = json_decode($contenido, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        return response()->json([
                            'mensaje' => 'Error al parsear el archivo JSON',
                            'error' => json_last_error_msg()
                        ], 422);
                    }
                }
            }
            // Caso 2: Datos JSON en el body
            elseif (isset($validated['datos'])) {
                $datos = $validated['datos'];
                $formato = 'json';
            }
            else {
                return response()->json([
                    'mensaje' => 'Debe enviar un archivo CSV/JSON o datos en el campo "datos"'
                ], 422);
            }

            // Procesar importación
            $resultado = $this->telegramaService->importarTelegramas($datos, $formato);

            $statusCode = $resultado['exito'] ? 201 : 422;
            return response()->json($resultado, $statusCode);

        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al importar telegramas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsear archivo CSV a array de telegramas
     */
    private function parsearCSV(string $rutaArchivo): array
    {
        $datos = [];
        $handle = fopen($rutaArchivo, 'r');

        // Leer encabezados
        $encabezados = fgetcsv($handle, 1000, ',');

        // Normalizar encabezados (quitar espacios, minúsculas)
        $encabezados = array_map(function($header) {
            return trim($header);
        }, $encabezados);

        // Leer filas
        while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($fila) !== count($encabezados)) {
                continue; // Saltar filas incompletas
            }

            $dato = array_combine($encabezados, $fila);
            $datos[] = $dato;
        }

        fclose($handle);
        return $datos;
    }
}