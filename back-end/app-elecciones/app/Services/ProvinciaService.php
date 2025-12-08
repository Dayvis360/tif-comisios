<?php

namespace App\Services;

use App\Repositories\ProvinciaRepository;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Collection;

/**
 * ProvinciaService
 * 
 * Responsabilidad:
 * - Orquestar los casos de uso relacionados con provincias
 * - Coordinar llamadas hacia el Modelo y el Repository
 * - NO conoce detalles de la base de datos ni del protocolo HTTP
 * - La lógica de negocio vive en el Modelo
 */
class ProvinciaService
{
    private ProvinciaRepository $provinciaRepository;

    public function __construct(ProvinciaRepository $provinciaRepository)
    {
        $this->provinciaRepository = $provinciaRepository;
    }

    /**
     * Caso de uso: Listar todas las provincias
     */
    public function listarProvincias(): Collection
    {
        // Simplemente delega al repositorio
        return $this->provinciaRepository->obtenerTodas();
    }

    /**
     * Caso de uso: Obtener una provincia por ID
     */
    public function obtenerProvincia(int $id): ?Provincia
    {
        return $this->provinciaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Registrar una nueva provincia
     */
    public function registrarProvincia(array $datos): Provincia
    {
        // 1. Crear el modelo de dominio con los datos recibidos
        $provincia = Provincia::crearDesdeRequest(
            $datos['nombre'],
            $datos['bancas_diputados'] ?? null,
            $datos['bancas_senadores'] ?? 3
        );

        // 2. Aplicar reglas de negocio en el modelo
        $provincia->verificarQueSeaValida($this->provinciaRepository);

        // 3. Guardar la provincia a través del repositorio
        $this->provinciaRepository->guardar($provincia);

        // 4. Devolver el modelo creado
        return $provincia;
    }

    /**
     * Caso de uso: Actualizar una provincia existente
     */
    public function actualizarProvincia(int $id, array $datos): Provincia
    {
        // 1. Buscar la provincia existente
        $provincia = $this->provinciaRepository->buscarPorId($id);

        if (!$provincia) {
            throw new \Exception("Provincia no encontrada con ID: {$id}");
        }

        // 2. Actualizar los datos del modelo
        $provincia->actualizarDatos(
            $datos['nombre'],
            $datos['bancas_diputados'] ?? $provincia->bancas_diputados,
            $datos['bancas_senadores'] ?? $provincia->bancas_senadores
        );

        // 3. Aplicar reglas de negocio
        $provincia->verificarQueSeaValida($this->provinciaRepository, $id);

        // 4. Guardar los cambios a través del repositorio
        $this->provinciaRepository->actualizar($provincia);

        // 5. Devolver el modelo actualizado
        return $provincia;
    }

    /**
     * Caso de uso: Eliminar una provincia
     */
    public function eliminarProvincia(int $id): array
    {
        // 1. Buscar la provincia
        $provincia = $this->provinciaRepository->buscarPorId($id);

        if (!$provincia) {
            throw new \Exception("Provincia no encontrada con ID: {$id}");
        }

        // 2. Verificar que se pueda eliminar (regla de negocio en el modelo)
        $provincia->verificarQueSeaPuedeEliminar();

        // 3. Eliminar a través del repositorio
        $this->provinciaRepository->eliminar($id);

        // 4. Retornar mensaje de éxito
        return ['mensaje' => 'Provincia eliminada correctamente'];
    }

    /**
     * Caso de uso: Verificar si existe una provincia con un nombre
     */
    public function existeProvinciaPorNombre(string $nombre, ?int $excludeId = null): bool
    {
        return $this->provinciaRepository->existeNombre($nombre, $excludeId);
    }
}
