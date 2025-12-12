<?php

namespace App\Repositories;

use App\DAO\ProvinciaDAO;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Collection;

//Repositorio de acceso a datos de provincias
class ProvinciaRepository
{
    private ProvinciaDAO $provinciaDAO;

    public function __construct(ProvinciaDAO $provinciaDAO)
    {
        $this->provinciaDAO = $provinciaDAO;
    }

    public function obtenerTodas(): Collection
    {
        return $this->provinciaDAO->obtenerTodas();
    }

    public function buscarPorId(int $id): ?Provincia
    {
        return $this->provinciaDAO->buscarPorId($id);
    }

    public function buscarPorNombre(string $nombre): ?Provincia
    {
        return $this->provinciaDAO->buscarPorNombre($nombre);
    }

    public function guardar(Provincia $provincia): void
    {
        $id = $this->provinciaDAO->insert([
            'nombre' => $provincia->nombre,
            'bancas_diputados' => $provincia->bancas_diputados,
            'bancas_senadores' => $provincia->bancas_senadores,
        ]);

        $provincia->id = $id;
    }

    public function actualizar(Provincia $provincia): bool
    {
        return $this->provinciaDAO->update($provincia->id, [
            'nombre' => $provincia->nombre,
            'bancas_diputados' => $provincia->bancas_diputados,
            'bancas_senadores' => $provincia->bancas_senadores,
        ]);
    }

    public function eliminar(int $id): bool
    {
        return $this->provinciaDAO->delete($id);
    }

    public function existeNombre(string $nombre, ?int $excludeId = null): bool
    {
        return $this->provinciaDAO->existeNombre($nombre, $excludeId);
    }

    public function contarProvincias(): int
    {
        return $this->provinciaDAO->count();
    }

    public function obtenerPorId(int $id): ?Provincia
    {
        return $this->buscarPorId($id);
    }
}
