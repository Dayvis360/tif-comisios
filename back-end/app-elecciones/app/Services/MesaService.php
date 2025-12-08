<?php

namespace App\Services;

use App\Repositories\MesaRepository;
use App\Models\Mesa;
use Illuminate\Database\Eloquent\Collection;

//Servicio de orquestación de casos de uso de mesas
class MesaService
{
    private MesaRepository $mesaRepository;

    public function __construct(MesaRepository $mesaRepository)
    {
        $this->mesaRepository = $mesaRepository;
    }

    //Listar todas las mesas
    public function listarMesas(): Collection
    {
        return $this->mesaRepository->obtenerTodas();
    }

    //Obtener mesa por ID
    public function obtenerMesa(int $id): ?Mesa
    {
        return $this->mesaRepository->buscarPorId($id);
    }

    //Registrar nueva mesa
    public function registrarMesa(array $datos): Mesa
    {
        $mesa = Mesa::crearDesdeRequest(
            $datos['provincia_id'],
            $datos['circuito'],
            $datos['establecimiento'],
            $datos['electores']
        );

        $mesa->verificarQueSeaValida($this->mesaRepository);
        $this->mesaRepository->guardar($mesa);
        return $this->mesaRepository->buscarPorId($mesa->id);
    }

    //Actualizar mesa existente
    public function actualizarMesa(int $id, array $datos): Mesa
    {
        $mesa = $this->mesaRepository->buscarPorId($id);

        if (!$mesa) {
            throw new \Exception("Mesa no encontrada con ID: {$id}");
        }

        $mesa->actualizarDatos(
            $datos['provincia_id'],
            $datos['circuito'],
            $datos['establecimiento'],
            $datos['electores']
        );

        $mesa->verificarQueSeaValida($this->mesaRepository, $id);
        $this->mesaRepository->actualizar($mesa);
        return $this->mesaRepository->buscarPorId($id);
    }

    //Eliminar mesa
    public function eliminarMesa(int $id): array
    {
        $mesa = $this->mesaRepository->buscarPorId($id);

        if (!$mesa) {
            throw new \Exception("Mesa no encontrada con ID: {$id}");
        }

        $mesa->verificarQueSeaPuedeEliminar();
        $this->mesaRepository->eliminar($id);

        return ['mensaje' => 'Mesa eliminada correctamente'];
    }
}
