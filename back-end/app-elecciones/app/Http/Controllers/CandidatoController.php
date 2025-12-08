<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CandidatoService;

//Controlador HTTP de candidatos
class CandidatoController extends Controller
{   
    private CandidatoService $candidatoService;

    public function __construct(CandidatoService $candidatoService)
    {
        $this->candidatoService = $candidatoService;
    }

    //Listar todos los candidatos
    public function index()
    {
        $candidatos = $this->candidatoService->listarCandidatos();
        return response()->json($candidatos, 200);
    }

    //Obtener candidato por ID
    public function show(Request $request, $id)
    {
        $candidato = $this->candidatoService->obtenerCandidato($id);

        if (!$candidato) {
            return response()->json(['mensaje' => 'Candidato no encontrado'], 404);
        }

        return response()->json($candidato, 200);
    }

    //Crear nuevo candidato
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'orden_en_lista' => 'required|integer|min:1',
            'lista_id' => 'required|exists:listas,id'
        ]);

        try {
            $candidato = $this->candidatoService->registrarCandidato($validated);
            return response()->json($candidato, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al registrar el candidato', 'error' => $e->getMessage()], 500);
        }
    }

    //Actualizar candidato
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'orden_en_lista' => 'required|integer|min:1',
            'lista_id' => 'required|exists:listas,id'
        ]);

        try {
            $candidato = $this->candidatoService->actualizarCandidato($id, $validated);
            return response()->json($candidato, 200);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensaje' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al actualizar el candidato', 'error' => $e->getMessage()], 500);
        }
    }

    //Eliminar candidato
    public function destroy($id)
    {
        try {
            $resultado = $this->candidatoService->eliminarCandidato($id);
            return response()->json($resultado, 200);
        } catch (\Exception $e) {
            return response()->json(['mensaje' => 'Error al eliminar el candidato', 'error' => $e->getMessage()], 400);
        }
    }
}
