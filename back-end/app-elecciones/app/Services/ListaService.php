<?php

namespace App\Services;

use App\Repositories\ListaRepository;
use App\Models\Lista;
use Illuminate\Database\Eloquent\Collection;

//Servicio de orquestación de casos de uso de listas
class ListaService
{
    private ListaRepository $listaRepository;

    public function __construct(ListaRepository $listaRepository)
    {
        $this->listaRepository = $listaRepository;
    }

    //Listar todas las listas
    public function listarListas(): Collection
    {
        return $this->listaRepository->obtenerTodas();
    }

    //Obtener lista por ID
    public function obtenerLista(int $id): ?Lista
    {
        return $this->listaRepository->buscarPorId($id);
    }

    //Registrar nueva lista
    public function registrarLista(array $datos): Lista
    {
        $lista = Lista::crearDesdeRequest(
            $datos['nombre'],
            $datos['cargo'],
            $datos['provincia_id'],
            $datos['alianza'] ?? null
        );

        $lista->verificarQueSeaValida($this->listaRepository);
        $this->listaRepository->guardar($lista);
        return $this->listaRepository->buscarPorId($lista->id);
    }

    //Actualizar lista existente
    public function actualizarLista(int $id, array $datos): Lista
    {
        $lista = $this->listaRepository->buscarPorId($id);

        if (!$lista) {
            throw new \Exception("Lista no encontrada con ID: {$id}");
        }

        $lista->actualizarDatos(
            $datos['nombre'],
            $datos['cargo'],
            $datos['provincia_id'],
            $datos['alianza'] ?? null
        );

        $lista->verificarQueSeaValida($this->listaRepository, $id);
        $this->listaRepository->actualizar($lista);
        return $this->listaRepository->buscarPorId($id);
    }

    //Eliminar lista
    public function eliminarLista(int $id): array
    {
        $lista = $this->listaRepository->buscarPorId($id);

        if (!$lista) {
            throw new \Exception("Lista no encontrada con ID: {$id}");
        }

        $lista->verificarQueSeaPuedeEliminar();
        $this->listaRepository->eliminar($id);

        return ['mensaje' => 'Lista eliminada correctamente'];
    }
}
