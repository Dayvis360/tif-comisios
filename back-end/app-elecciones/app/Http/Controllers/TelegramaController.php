<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TelegramaService;

//Controlador HTTP de telegramas
class TelegramaController extends Controller
{
    private TelegramaService $telegramaService;

    public function __construct(TelegramaService $telegramaService)
    {
        $this->telegramaService = $telegramaService;
    }

    //Listar telegramas con filtros opcionales
    public function index(Request $request)
    {
        if (!$request->hasAny(['provincia_id', 'cargo', 'lista_id', 'mesa_desde', 'mesa_hasta'])) {
            $telegramas = $this->telegramaService->listarTelegramas();
            return response()->json($telegramas, 200);
        }

        $validated = $request->validate([
            'provincia_id' => 'nullable|integer|exists:provincias,id',
            'cargo' => 'nullable|string|in:DIPUTADOS,SENADORES',
            'lista_id' => 'nullable|integer|exists:listas,id',
            'mesa_desde' => 'nullable|integer|exists:mesas,id',
            'mesa_hasta' => 'nullable|integer|exists:mesas,id'
        ]);

        $telegramas = $this->telegramaService->listarTelegramasConFiltros(
            $validated['provincia_id'] ?? null,
            $validated['cargo'] ?? null,
            $validated['lista_id'] ?? null,
            $validated['mesa_desde'] ?? null,
            $validated['mesa_hasta'] ?? null
        );

        return response()->json($telegramas, 200);
    }

    //Obtener telegrama por ID
    public function show(Request $request, $id)
    {
        $telegrama = $this->telegramaService->obtenerTelegrama($id);

        if (!$telegrama) {
            return response()->json(['mensaje' => 'Telegrama no encontrado'], 404);
        }

        return response()->json($telegrama, 200);
    }

    //Crear nuevo telegrama
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

    //Actualizar telegrama
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

    //Eliminar telegrama
    public function destroy($id)
    {
        try {
            $resultado = $this->telegramaService->eliminarTelegrama($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al eliminar el telegrama', 'error' => $e->getMessage()], 400);
        }
    }

    //Importar telegramas desde archivo CSV o JSON
    public function importar(Request $request)
    {
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
            elseif (isset($validated['datos'])) {
                $datos = $validated['datos'];
                $formato = 'json';
            }
            else {
                return response()->json([
                    'mensaje' => 'Debe enviar un archivo CSV/JSON o datos en el campo "datos"'
                ], 422);
            }

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

    //Parsear archivo CSV a array de telegramas
    private function parsearCSV(string $rutaArchivo): array
    {
        $datos = [];
        $handle = fopen($rutaArchivo, 'r');

        $encabezados = fgetcsv($handle, 1000, ',');

        $encabezados = array_map(function($header) {
            return trim($header);
        }, $encabezados);

        while (($fila = fgetcsv($handle, 1000, ',')) !== false) {
            if (count($fila) !== count($encabezados)) {
                continue;
            }

            $dato = array_combine($encabezados, $fila);
            $datos[] = $dato;
        }

        fclose($handle);
        return $datos;
    }
}