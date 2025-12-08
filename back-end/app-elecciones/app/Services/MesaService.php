<?php

namespace App\Services;

use App\Repositories\MesaRepository;
use App\Models\Mesa;
use Illuminate\Database\Eloquent\Collection;

/**
 * MesaService
 * 
 * Responsabilidad:
 * - Orquestar los casos de uso relacionados con mesas
 * - Coordinar llamadas hacia el Modelo y el Repository
 * - NO conoce detalles de la base de datos ni del protocolo HTTP
 * - La lógica de negocio vive en el Modelo
 */
class MesaService
{
    private MesaRepository $mesaRepository;

    public function __construct(MesaRepository $mesaRepository)
    {
        $this->mesaRepository = $mesaRepository;
    }

    /**
     * Caso de uso: Listar todas las mesas
     */
    public function listarMesas(): Collection
    {
        return $this->mesaRepository->obtenerTodas();
    }

    /**
     * Caso de uso: Obtener una mesa por ID
     */
    public function obtenerMesa(int $id): ?Mesa
    {
        return $this->mesaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Registrar una nueva mesa
     */
    public function registrarMesa(array $datos): Mesa
    {
        // 1. Crear el modelo de dominio
        $mesa = Mesa::crearDesdeRequest(
            $datos['provincia_id'],
            $datos['circuito'],
            $datos['establecimiento'],
            $datos['electores']
        );

        // 2. Aplicar reglas de negocio
        $mesa->verificarQueSeaValida($this->mesaRepository);

        // 3. Guardar a través del repositorio
        $this->mesaRepository->guardar($mesa);

        // 4. Recargar con relaciones
        return $this->mesaRepository->buscarPorId($mesa->id);
    }

    /**
     * Caso de uso: Actualizar una mesa existente
     */
    public function actualizarMesa(int $id, array $datos): Mesa
    {
        // 1. Buscar la mesa existente
        $mesa = $this->mesaRepository->buscarPorId($id);

        if (!$mesa) {
            throw new \Exception("Mesa no encontrada con ID: {$id}");
        }

        // 2. Actualizar los datos
        $mesa->actualizarDatos(
            $datos['provincia_id'],
            $datos['circuito'],
            $datos['establecimiento'],
            $datos['electores']
        );

        // 3. Aplicar reglas de negocio
        $mesa->verificarQueSeaValida($this->mesaRepository, $id);

        // 4. Guardar cambios
        $this->mesaRepository->actualizar($mesa);

        // 5. Retornar con relaciones actualizadas
        return $this->mesaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Eliminar una mesa
     */
    public function eliminarMesa(int $id): array
    {
        // 1. Buscar la mesa
        $mesa = $this->mesaRepository->buscarPorId($id);

        if (!$mesa) {
            throw new \Exception("Mesa no encontrada con ID: {$id}");
        }

        // 2. Verificar que se pueda eliminar
        $mesa->verificarQueSeaPuedeEliminar();

        // 3. Eliminar
        $this->mesaRepository->eliminar($id);

        return ['mensaje' => 'Mesa eliminada correctamente'];
    }
}
