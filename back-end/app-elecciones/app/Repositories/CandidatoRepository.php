<?php

namespace App\Repositories;

use App\DAO\CandidatoDAO;
use App\Models\Candidato;
use Illuminate\Database\Eloquent\Collection;

/**
 * CandidatoRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos orientados al dominio
 * - Trabajar con objetos del dominio (Modelo Candidato)
 */
class CandidatoRepository
{
    private CandidatoDAO $candidatoDAO;

    public function __construct(CandidatoDAO $candidatoDAO)
    {
        $this->candidatoDAO = $candidatoDAO;
    }

    /**
     * Obtener todos los candidatos
     */
    public function obtenerTodos(): Collection
    {
        return Candidato::with('lista.provincia')
            ->orderBy('lista_id')
            ->orderBy('orden_en_lista')
            ->get();
    }

    /**
     * Buscar candidato por ID
     */
    public function buscarPorId(int $id): ?Candidato
    {
        return Candidato::with('lista.provincia')->find($id);
    }

    /**
     * Buscar candidatos por lista
     */
    public function buscarPorLista(int $listaId): Collection
    {
        return Candidato::where('lista_id', $listaId)
            ->orderBy('orden_en_lista')
            ->get();
    }

    /**
     * Guardar un nuevo candidato
     */
    public function guardar(Candidato $candidato): void
    {
        $id = $this->candidatoDAO->insert([
            'nombre' => $candidato->nombre,
            'orden_en_lista' => $candidato->orden_en_lista,
            'lista_id' => $candidato->lista_id,
        ]);

        $candidato->id = $id;
    }

    /**
     * Actualizar un candidato existente
     */
    public function actualizar(Candidato $candidato): bool
    {
        return $this->candidatoDAO->update($candidato->id, [
            'nombre' => $candidato->nombre,
            'orden_en_lista' => $candidato->orden_en_lista,
            'lista_id' => $candidato->lista_id,
        ]);
    }

    /**
     * Eliminar un candidato
     */
    public function eliminar(int $id): bool
    {
        return $this->candidatoDAO->delete($id);
    }

    /**
     * Verificar si existe orden duplicado en lista
     */
    public function existeOrdenEnLista(int $listaId, int $orden, ?int $excludeId = null): bool
    {
        return $this->candidatoDAO->existeOrdenEnLista($listaId, $orden, $excludeId);
    }

    /**
     * Obtener el siguiente orden disponible en una lista
     */
    public function obtenerSiguienteOrden(int $listaId): int
    {
        return $this->candidatoDAO->getMaxOrdenEnLista($listaId) + 1;
    }
}
