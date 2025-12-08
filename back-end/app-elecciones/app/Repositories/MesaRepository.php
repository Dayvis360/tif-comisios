<?php

namespace App\Repositories;

use App\DAO\MesaDAO;
use App\Models\Mesa;
use Illuminate\Database\Eloquent\Collection;

//Repositorio de acceso a datos de mesas
class MesaRepository
{
    private MesaDAO $mesaDAO;

    public function __construct(MesaDAO $mesaDAO)
    {
        $this->mesaDAO = $mesaDAO;
    }

    public function obtenerTodas(): Collection
    {
        return Mesa::with('provincia')->orderBy('provincia_id')->orderBy('circuito')->get();
    }

    public function buscarPorId(int $id): ?Mesa
    {
        return Mesa::with('provincia')->find($id);
    }

    public function buscarPorProvincia(int $provinciaId): Collection
    {
        return Mesa::where('provincia_id', $provinciaId)
            ->orderBy('circuito')
            ->get();
    }

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

    public function actualizar(Mesa $mesa): bool
    {
        return $this->mesaDAO->update($mesa->id, [
            'provincia_id' => $mesa->provincia_id,
            'circuito' => $mesa->circuito,
            'establecimiento' => $mesa->establecimiento,
            'electores' => $mesa->electores,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->mesaDAO->delete($id);
    }

    public function existeMesaEnCircuito(int $provinciaId, string $circuito, string $establecimiento, ?int $excludeId = null): bool
    {
        return $this->mesaDAO->existeMesaEnCircuito($provinciaId, $circuito, $establecimiento, $excludeId);
    }

    public function contarMesas(): int
    {
        return $this->mesaDAO->count();
    }
}
