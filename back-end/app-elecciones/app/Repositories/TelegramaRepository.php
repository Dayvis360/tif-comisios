<?php

namespace App\Repositories;

use App\DAO\TelegramaDAO;
use App\Models\Telegrama;
use Illuminate\Database\Eloquent\Collection;

//Repositorio de acceso a datos de telegramas
class TelegramaRepository
{
    private TelegramaDAO $telegramaDAO;

    public function __construct(TelegramaDAO $telegramaDAO)
    {
        $this->telegramaDAO = $telegramaDAO;
    }

    public function obtenerTodos(): Collection
    {
        return $this->telegramaDAO->obtenerTodos();
    }

    public function buscarPorId(int $id): ?Telegrama
    {
        return $this->telegramaDAO->buscarPorId($id);
    }

    public function buscarPorMesa(int $mesaId): Collection
    {
        return $this->telegramaDAO->buscarPorMesa($mesaId);
    }

    public function guardar(Telegrama $telegrama): void
    {
        $id = $this->telegramaDAO->insert([
            'mesa_id' => $telegrama->mesa_id,
            'lista_id' => $telegrama->lista_id,
            'votos_Diputados' => $telegrama->votos_Diputados,
            'votos_Senadores' => $telegrama->votos_Senadores,
            'voto_Blancos' => $telegrama->voto_Blancos,
            'voto_Nulos' => $telegrama->voto_Nulos,
            'voto_Recurridos' => $telegrama->voto_Recurridos,
            'usuario_carga' => $telegrama->usuario_carga,
            'fecha_carga' => $telegrama->fecha_carga,
        ]);

        $telegrama->id = $id;
    }

    public function actualizar(Telegrama $telegrama): bool
    {
        return $this->telegramaDAO->update($telegrama->id, [
            'mesa_id' => $telegrama->mesa_id,
            'lista_id' => $telegrama->lista_id,
            'votos_Diputados' => $telegrama->votos_Diputados,
            'votos_Senadores' => $telegrama->votos_Senadores,
            'voto_Blancos' => $telegrama->voto_Blancos,
            'voto_Nulos' => $telegrama->voto_Nulos,
            'voto_Recurridos' => $telegrama->voto_Recurridos,
            'usuario_modificacion' => $telegrama->usuario_modificacion,
            'fecha_modificacion' => $telegrama->fecha_modificacion,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->telegramaDAO->delete($id);
    }

    public function existeTelegramaParaMesaYLista(int $mesaId, int $listaId, ?int $excludeId = null): bool
    {
        return $this->telegramaDAO->existeTelegramaParaMesaYLista($mesaId, $listaId, $excludeId);
    }

    public function obtenerTotalVotosMesa(int $mesaId): object
    {
        return $this->telegramaDAO->getTotalVotosMesa($mesaId);
    }

    public function obtenerPorLista(int $listaId): Collection
    {
        return $this->telegramaDAO->buscarPorLista($listaId);
    }

    public function guardarLote(array $telegramas): bool
    {
        return $this->telegramaDAO->insertBatch($telegramas);
    }

    public function obtenerConFiltros(?int $provinciaId = null, ?string $cargo = null, ?int $listaId = null, ?int $mesaDesde = null, ?int $mesaHasta = null): Collection
    {
        $query = Telegrama::with(['mesa.provincia', 'lista']);

        if ($provinciaId !== null) {
            $query->whereHas('mesa', function($q) use ($provinciaId) {
                $q->where('provincia_id', $provinciaId);
            });
        }

        if ($cargo !== null) {
            $query->whereHas('lista', function($q) use ($cargo) {
                $q->where('cargo', $cargo);
            });
        }

        if ($listaId !== null) {
            $query->where('lista_id', $listaId);
        }

        if ($mesaDesde !== null) {
            $query->where('mesa_id', '>=', $mesaDesde);
        }

        if ($mesaHasta !== null) {
            $query->where('mesa_id', '<=', $mesaHasta);
        }

        return $query->orderBy('mesa_id')->orderBy('lista_id')->get();
    }
}
