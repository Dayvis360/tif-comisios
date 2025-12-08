<?php

namespace App\Repositories;

use App\DAO\ProvinciaDAO;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Collection;

/**
 * ProvinciaRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos orientados al dominio
 * - Ocultar cómo se hace el acceso real a la BD (usando el DAO)
 * - Trabajar con objetos del dominio (Modelo Provincia)
 * - NO contiene lógica de negocio (eso va en el Modelo)
 */
class ProvinciaRepository
{
    private ProvinciaDAO $provinciaDAO;

    public function __construct(ProvinciaDAO $provinciaDAO)
    {
        $this->provinciaDAO = $provinciaDAO;
    }

    /**
     * Obtener todas las provincias
     */
    public function obtenerTodas(): Collection
    {
        // Usar Eloquent para aprovechar las relaciones y métodos del modelo
        return Provincia::orderBy('nombre', 'asc')->get();
    }

    /**
     * Buscar provincia por ID
     */
    public function buscarPorId(int $id): ?Provincia
    {
        return Provincia::find($id);
    }

    /**
     * Buscar provincia por nombre
     */
    public function buscarPorNombre(string $nombre): ?Provincia
    {
        return Provincia::where('nombre', $nombre)->first();
    }

    /**
     * Guardar una nueva provincia
     */
    public function guardar(Provincia $provincia): void
    {
        $id = $this->provinciaDAO->insert([
            'nombre' => $provincia->nombre,
            'bancas_diputados' => $provincia->bancas_diputados,
            'bancas_senadores' => $provincia->bancas_senadores,
        ]);

        // Actualizar el modelo con el ID generado
        $provincia->id = $id;
    }

    /**
     * Actualizar una provincia existente
     */
    public function actualizar(Provincia $provincia): bool
    {
        return $this->provinciaDAO->update($provincia->id, [
            'nombre' => $provincia->nombre,
            'bancas_diputados' => $provincia->bancas_diputados,
            'bancas_senadores' => $provincia->bancas_senadores,
        ]);
    }

    /**
     * Eliminar una provincia
     */
    public function eliminar(int $id): bool
    {
        return $this->provinciaDAO->delete($id);
    }

    /**
     * Verificar si existe una provincia con el nombre dado
     */
    public function existeNombre(string $nombre, ?int $excludeId = null): bool
    {
        return $this->provinciaDAO->existeNombre($nombre, $excludeId);
    }

    /**
     * Contar provincias
     */
    public function contarProvincias(): int
    {
        return $this->provinciaDAO->count();
    }

    /**
     * Obtener provincia por ID (alias para uso en servicios)
     */
    public function obtenerPorId(int $id): ?Provincia
    {
        return $this->buscarPorId($id);
    }
}
