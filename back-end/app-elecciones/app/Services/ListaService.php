<?php

namespace App\Services;

use App\Repositories\ListaRepository;
use App\Models\Lista;
use Illuminate\Database\Eloquent\Collection;

/**
 * ListaService
 * 
 * Responsabilidad:
 * - Orquestar los casos de uso relacionados con listas
 * - Coordinar llamadas hacia el Modelo y el Repository
 */
class ListaService
{
    private ListaRepository $listaRepository;

    public function __construct(ListaRepository $listaRepository)
    {
        $this->listaRepository = $listaRepository;
    }

    /**
     * Caso de uso: Listar todas las listas
     */
    public function listarListas(): Collection
    {
        return $this->listaRepository->obtenerTodas();
    }

    /**
     * Caso de uso: Obtener una lista por ID
     */
    public function obtenerLista(int $id): ?Lista
    {
        return $this->listaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Registrar una nueva lista
     */
    public function registrarLista(array $datos): Lista
    {
        // 1. Crear el modelo de dominio
        $lista = Lista::crearDesdeRequest(
            $datos['nombre'],
            $datos['cargo'],
            $datos['provincia_id'],
            $datos['alianza'] ?? null
        );

        // 2. Aplicar reglas de negocio
        $lista->verificarQueSeaValida($this->listaRepository);

        // 3. Guardar
        $this->listaRepository->guardar($lista);

        // 4. Retornar con relaciones
        return $this->listaRepository->buscarPorId($lista->id);
    }

    /**
     * Caso de uso: Actualizar una lista existente
     */
    public function actualizarLista(int $id, array $datos): Lista
    {
        // 1. Buscar la lista
        $lista = $this->listaRepository->buscarPorId($id);

        if (!$lista) {
            throw new \Exception("Lista no encontrada con ID: {$id}");
        }

        // 2. Actualizar datos
        $lista->actualizarDatos(
            $datos['nombre'],
            $datos['cargo'],
            $datos['provincia_id'],
            $datos['alianza'] ?? null
        );

        // 3. Aplicar reglas de negocio
        $lista->verificarQueSeaValida($this->listaRepository, $id);

        // 4. Guardar cambios
        $this->listaRepository->actualizar($lista);

        // 5. Retornar actualizada
        return $this->listaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Eliminar una lista
     */
    public function eliminarLista(int $id): array
    {
        // 1. Buscar la lista
        $lista = $this->listaRepository->buscarPorId($id);

        if (!$lista) {
            throw new \Exception("Lista no encontrada con ID: {$id}");
        }

        // 2. Verificar que se pueda eliminar
        $lista->verificarQueSeaPuedeEliminar();

        // 3. Eliminar
        $this->listaRepository->eliminar($id);

        return ['mensaje' => 'Lista eliminada correctamente'];
    }
}
