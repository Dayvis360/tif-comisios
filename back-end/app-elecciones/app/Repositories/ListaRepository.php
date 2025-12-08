<?php

namespace App\Repositories;

use App\DAO\ListaDAO;
use App\Models\Lista;
use Illuminate\Database\Eloquent\Collection;

/**
 * ListaRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos orientados al dominio
 * - Trabajar con objetos del dominio (Modelo Lista)
 */
class ListaRepository
{
    private ListaDAO $listaDAO;

    public function __construct(ListaDAO $listaDAO)
    {
        $this->listaDAO = $listaDAO;
    }

    /**
     * Obtener todas las listas
     */
    public function obtenerTodas(): Collection
    {
        return Lista::with('provincia')->orderBy('provincia_id')->orderBy('nombre')->get();
    }

    /**
     * Buscar lista por ID
     */
    public function buscarPorId(int $id): ?Lista
    {
        return Lista::with('provincia')->find($id);
    }

    /**
     * Buscar listas por provincia
     */
    public function buscarPorProvincia(int $provinciaId): Collection
    {
        return Lista::where('provincia_id', $provinciaId)->get();
    }

    /**
     * Guardar una nueva lista
     */
    public function guardar(Lista $lista): void
    {
        $id = $this->listaDAO->insert([
            'nombre' => $lista->nombre,
            'alianza' => $lista->alianza,
            'cargo' => $lista->cargo,
            'provincia_id' => $lista->provincia_id,
        ]);

        $lista->id = $id;
    }

    /**
     * Actualizar una lista existente
     */
    public function actualizar(Lista $lista): bool
    {
        return $this->listaDAO->update($lista->id, [
            'nombre' => $lista->nombre,
            'alianza' => $lista->alianza,
            'cargo' => $lista->cargo,
            'provincia_id' => $lista->provincia_id,
        ]);
    }

    /**
     * Eliminar una lista
     */
    public function eliminar(int $id): bool
    {
        return $this->listaDAO->delete($id);
    }

    /**
     * Verificar si existe lista duplicada
     */
    public function existeListaEnProvincia(string $nombre, int $provinciaId, string $cargo, ?int $excludeId = null): bool
    {
        return $this->listaDAO->existeListaEnProvincia($nombre, $provinciaId, $cargo, $excludeId);
    }

    /**
     * Obtener listas por provincia y cargo (para cálculo D'Hont)
     */
    public function obtenerPorProvinciaYCargo(int $provinciaId, string $cargo): Collection
    {
        return Lista::where('provincia_id', $provinciaId)
            ->where('cargo', $cargo)
            ->get();
    }
}
