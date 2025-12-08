<?php

namespace App\Repositories;

use App\DAO\CandidatoDAO;
use App\Models\Candidato;
use Illuminate\Database\Eloquent\Collection;

//Repositorio de acceso a datos de candidatos
class CandidatoRepository
{
    private CandidatoDAO $candidatoDAO;

    public function __construct(CandidatoDAO $candidatoDAO)
    {
        $this->candidatoDAO = $candidatoDAO;
    }

    public function obtenerTodos(): Collection
    {
        return Candidato::with('lista.provincia')
            ->orderBy('lista_id')
            ->orderBy('orden_en_lista')
            ->get();
    }

    public function buscarPorId(int $id): ?Candidato
    {
        return Candidato::with('lista.provincia')->find($id);
    }

    public function buscarPorLista(int $listaId): Collection
    {
        return Candidato::where('lista_id', $listaId)
            ->orderBy('orden_en_lista')
            ->get();
    }

    public function guardar(Candidato $candidato): void
    {
        $id = $this->candidatoDAO->insert([
            'nombre' => $candidato->nombre,
            'orden_en_lista' => $candidato->orden_en_lista,
            'lista_id' => $candidato->lista_id,
        ]);

        $candidato->id = $id;
    }

    public function actualizar(Candidato $candidato): bool
    {
        return $this->candidatoDAO->update($candidato->id, [
            'nombre' => $candidato->nombre,
            'orden_en_lista' => $candidato->orden_en_lista,
            'lista_id' => $candidato->lista_id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->candidatoDAO->delete($id);
    }

    public function existeOrdenEnLista(int $listaId, int $orden, ?int $excludeId = null): bool
    {
        return $this->candidatoDAO->existeOrdenEnLista($listaId, $orden, $excludeId);
    }

    public function obtenerSiguienteOrden(int $listaId): int
    {
        return $this->candidatoDAO->getMaxOrdenEnLista($listaId) + 1;
    }
}
