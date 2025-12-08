<?php

namespace App\Repositories;

use App\DAO\ListaDAO;
use App\Models\Lista;
use Illuminate\Database\Eloquent\Collection;

//Repositorio de acceso a datos de listas electorales
class ListaRepository
{
    private ListaDAO $listaDAO;

    public function __construct(ListaDAO $listaDAO)
    {
        $this->listaDAO = $listaDAO;
    }

    public function obtenerTodas(): Collection
    {
        return Lista::with('provincia')->orderBy('provincia_id')->orderBy('nombre')->get();
    }

    public function buscarPorId(int $id): ?Lista
    {
        return Lista::with('provincia')->find($id);
    }

    public function buscarPorProvincia(int $provinciaId): Collection
    {
        return Lista::where('provincia_id', $provinciaId)->get();
    }

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

    public function actualizar(Lista $lista): bool
    {
        return $this->listaDAO->update($lista->id, [
            'nombre' => $lista->nombre,
            'alianza' => $lista->alianza,
            'cargo' => $lista->cargo,
            'provincia_id' => $lista->provincia_id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->listaDAO->delete($id);
    }

    public function existeListaEnProvincia(string $nombre, int $provinciaId, string $cargo, ?int $excludeId = null): bool
    {
        return $this->listaDAO->existeListaEnProvincia($nombre, $provinciaId, $cargo, $excludeId);
    }

    public function obtenerPorProvinciaYCargo(int $provinciaId, string $cargo): Collection
    {
        return Lista::where('provincia_id', $provinciaId)
            ->where('cargo', $cargo)
            ->get();
    }
}
