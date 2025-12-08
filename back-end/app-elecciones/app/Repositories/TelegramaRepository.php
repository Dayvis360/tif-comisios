<?php

namespace App\Repositories;

use App\DAO\TelegramaDAO;
use App\Models\Telegrama;
use Illuminate\Database\Eloquent\Collection;

/**
 * TelegramaRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos orientados al dominio
 * - Trabajar con objetos del dominio (Modelo Telegrama)
 */
class TelegramaRepository
{
    private TelegramaDAO $telegramaDAO;

    public function __construct(TelegramaDAO $telegramaDAO)
    {
        $this->telegramaDAO = $telegramaDAO;
    }

    /**
     * Obtener todos los telegramas
     */
    public function obtenerTodos(): Collection
    {
        return Telegrama::with(['mesa.provincia', 'lista'])
            ->orderBy('mesa_id')
            ->orderBy('lista_id')
            ->get();
    }

    /**
     * Buscar telegrama por ID
     */
    public function buscarPorId(int $id): ?Telegrama
    {
        return Telegrama::with(['mesa.provincia', 'lista'])->find($id);
    }

    /**
     * Buscar telegramas por mesa
     */
    public function buscarPorMesa(int $mesaId): Collection
    {
        return Telegrama::with('lista')
            ->where('mesa_id', $mesaId)
            ->get();
    }

    /**
     * Guardar un nuevo telegrama
     */
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

    /**
     * Actualizar un telegrama existente
     */
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

    /**
     * Eliminar un telegrama
     */
    public function eliminar(int $id): bool
    {
        return $this->telegramaDAO->delete($id);
    }

    /**
     * Verificar si existe telegrama duplicado
     */
    public function existeTelegramaParaMesaYLista(int $mesaId, int $listaId, ?int $excludeId = null): bool
    {
        return $this->telegramaDAO->existeTelegramaParaMesaYLista($mesaId, $listaId, $excludeId);
    }

    /**
     * Obtener total de votos de una mesa (para validaciones)
     */
    public function obtenerTotalVotosMesa(int $mesaId): object
    {
        return $this->telegramaDAO->getTotalVotosMesa($mesaId);
    }

    /**
     * Obtener telegramas por lista (para cálculo D'Hont)
     */
    public function obtenerPorLista(int $listaId): Collection
    {
        return Telegrama::where('lista_id', $listaId)->get();
    }

    /**
     * Guardar múltiples telegramas en lote
     */
    public function guardarLote(array $telegramas): bool
    {
        return $this->telegramaDAO->insertBatch($telegramas);
    }

    /**
     * Obtener telegramas con filtros
     */
    public function obtenerConFiltros(?int $provinciaId = null, ?string $cargo = null, ?int $listaId = null, ?int $mesaDesde = null, ?int $mesaHasta = null): Collection
    {
        $query = Telegrama::with(['mesa.provincia', 'lista']);

        // Filtro por provincia
        if ($provinciaId !== null) {
            $query->whereHas('mesa', function($q) use ($provinciaId) {
                $q->where('provincia_id', $provinciaId);
            });
        }

        // Filtro por cargo (a través de lista)
        if ($cargo !== null) {
            $query->whereHas('lista', function($q) use ($cargo) {
                $q->where('cargo', $cargo);
            });
        }

        // Filtro por lista
        if ($listaId !== null) {
            $query->where('lista_id', $listaId);
        }

        // Filtro por rango de mesas
        if ($mesaDesde !== null) {
            $query->where('mesa_id', '>=', $mesaDesde);
        }

        if ($mesaHasta !== null) {
            $query->where('mesa_id', '<=', $mesaHasta);
        }

        return $query->orderBy('mesa_id')->orderBy('lista_id')->get();
    }
}
