<?php

namespace App\DAO;

use App\Models\Lista;

class ListaDAO
{
    //Obtener todas las listas
    public function getAll(): array
    {
        return Lista::with('provincia')
            ->orderBy('provincia_id', 'asc')
            ->orderBy('nombre', 'asc')
            ->get()
            ->map(function($lista) {
                $array = $lista->toArray();
                $array['provincia_nombre'] = $lista->provincia->nombre ?? null;
                return $array;
            })
            ->toArray();
    }

    //Buscar lista por ID
    public function findById(int $id): ?object
    {
        $lista = Lista::find($id);
        return $lista ? (object)$lista->toArray() : null;
    }

    //Buscar listas por provincia
    public function findByProvincia(int $provinciaId): array
    {
        return Lista::where('provincia_id', $provinciaId)
            ->get()
            ->toArray();
    }

    //Buscar listas por cargo
    public function findByCargo(string $cargo): array
    {
        return Lista::where('cargo', $cargo)
            ->get()
            ->toArray();
    }

    //Insertar nueva lista
    public function insert(array $data): int
    {
        $lista = Lista::create([
            'nombre' => $data['nombre'],
            'alianza' => $data['alianza'] ?? null,
            'cargo' => $data['cargo'],
            'provincia_id' => $data['provincia_id'],
        ]);
        
        return $lista->id;
    }

    //Actualizar lista
    public function update(int $id, array $data): bool
    {
        return Lista::where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'alianza' => $data['alianza'] ?? null,
                'cargo' => $data['cargo'],
                'provincia_id' => $data['provincia_id'],
            ]);
    }

    //Eliminar lista
    public function delete(int $id): bool
    {
        return Lista::destroy($id) > 0;
    }

    //Verificar si existe lista en provincia
    public function existeListaEnProvincia(string $nombre, int $provinciaId, string $cargo, ?int $excludeId = null): bool
    {
        $query = Lista::where('nombre', $nombre)
            ->where('provincia_id', $provinciaId)
            ->where('cargo', $cargo);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    //Contar listas
    public function count(): int
    {
        return Lista::count();
    }
}
