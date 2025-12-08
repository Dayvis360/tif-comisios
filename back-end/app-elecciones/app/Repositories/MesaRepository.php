<?php

namespace App\Repositories;

use App\DAO\MesaDAO;
use App\Models\Mesa;
use Illuminate\Database\Eloquent\Collection;

/**
 * MesaRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos orientados al dominio
 * - Ocultar cómo se hace el acceso real a la BD (usando el DAO)
 * - Trabajar con objetos del dominio (Modelo Mesa)
 * - NO contiene lógica de negocio (eso va en el Modelo)
 */
class MesaRepository
{
    private MesaDAO $mesaDAO;

    public function __construct(MesaDAO $mesaDAO)
    {
        $this->mesaDAO = $mesaDAO;
    }

    /**
     * Obtener todas las mesas con relaciones
     */
    public function obtenerTodas(): Collection
    {
        return Mesa::with('provincia')->orderBy('provincia_id')->orderBy('circuito')->get();
    }

    /**
     * Buscar mesa por ID
     */
    public function buscarPorId(int $id): ?Mesa
    {
        return Mesa::with('provincia')->find($id);
    }

    /**
     * Buscar mesas por provincia
     */
    public function buscarPorProvincia(int $provinciaId): Collection
    {
        return Mesa::where('provincia_id', $provinciaId)
            ->orderBy('circuito')
            ->get();
    }

    /**
     * Guardar una nueva mesa
     */
    public function guardar(Mesa $mesa): void
    {
        $id = $this->mesaDAO->insert([
            'provincia_id' => $mesa->provincia_id,
            'circuito' => $mesa->circuito,
            'establecimiento' => $mesa->establecimiento,
            'electores' => $mesa->electores,
        ]);

        $mesa->id = $id;
    }

    /**
     * Actualizar una mesa existente
     */
    public function actualizar(Mesa $mesa): bool
    {
        return $this->mesaDAO->update($mesa->id, [
            'provincia_id' => $mesa->provincia_id,
            'circuito' => $mesa->circuito,
            'establecimiento' => $mesa->establecimiento,
            'electores' => $mesa->electores,
        ]);
    }

    /**
     * Eliminar una mesa
     */
    public function eliminar(int $id): bool
    {
        return $this->mesaDAO->delete($id);
    }

    /**
     * Verificar si existe mesa duplicada
     */
    public function existeMesaEnCircuito(int $provinciaId, string $circuito, string $establecimiento, ?int $excludeId = null): bool
    {
        return $this->mesaDAO->existeMesaEnCircuito($provinciaId, $circuito, $establecimiento, $excludeId);
    }

    /**
     * Contar mesas
     */
    public function contarMesas(): int
    {
        return $this->mesaDAO->count();
    }
}
