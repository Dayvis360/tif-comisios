<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MesaService;

//Controlador HTTP de mesas
class MesaController extends Controller
{
    private MesaService $mesaService;

    public function __construct(MesaService $mesaService)
    {
        $this->mesaService = $mesaService;
    }

    //Listar todas las mesas
    public function index()
    {
        $mesas = $this->mesaService->listarMesas();
        return response()->json($mesas, 200);
    }

    //Obtener mesa por ID
    public function show($id)
    {
        $mesa = $this->mesaService->obtenerMesa($id);

        if (!$mesa) {
            return response()->json(['mensaje' => 'Mesa no encontrada'], 404);
        }

        return response()->json($mesa, 200);
    }

    //Crear nueva mesa
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

    //Actualizar mesa
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

    //Eliminar mesa
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