<?php

namespace App\DAO;

use App\Models\Provincia;

class ProvinciaDAO
{
    //Obtener todas las provincias
    public function getAll(): array
    {
        return Provincia::orderBy('nombre', 'asc')
            ->get()
            ->toArray();
    }

    //Buscar provincia por ID
    public function findById(int $id): ?object
    {
        $provincia = Provincia::find($id);
        return $provincia ? (object)$provincia->toArray() : null;
    }

    //Buscar provincia por nombre
    public function findByNombre(string $nombre): ?object
    {
        $provincia = Provincia::where('nombre', $nombre)->first();
        return $provincia ? (object)$provincia->toArray() : null;
    }

    //Insertar nueva provincia
    public function insert(array $data): int
    {
        $provincia = Provincia::create([
            'nombre' => $data['nombre'],
            'bancas_diputados' => $data['bancas_diputados'] ?? null,
            'bancas_senadores' => $data['bancas_senadores'] ?? 3,
        ]);
        
        return $provincia->id;
    }

    //Actualizar provincia
    public function update(int $id, array $data): bool
    {
        return Provincia::where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'bancas_diputados' => $data['bancas_diputados'] ?? null,
                'bancas_senadores' => $data['bancas_senadores'] ?? 3,
            ]);
    }

    //Eliminar provincia
    public function delete(int $id): bool
    {
        return Provincia::destroy($id) > 0;
    }

    //Verificar si existe nombre de provincia
    public function existeNombre(string $nombre, ?int $excludeId = null): bool
    {
        $query = Provincia::where('nombre', $nombre);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    //Contar provincias
    public function count(): int
    {
        return Provincia::count();
    }
}
