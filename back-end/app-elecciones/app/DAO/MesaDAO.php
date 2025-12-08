<?php

namespace App\DAO;

use App\Models\Mesa;

class MesaDAO
{
    //Obtener todas las mesas con provincia
    public function getAll(): array
    {
        return Mesa::with('provincia')
            ->orderBy('provincia_id', 'asc')
            ->orderBy('circuito', 'asc')
            ->get()
            ->map(function($mesa) {
                $array = $mesa->toArray();
                $array['provincia_nombre'] = $mesa->provincia->nombre ?? null;
                return $array;
            })
            ->toArray();
    }

    //Buscar mesa por ID
    public function findById(int $id): ?object
    {
        $mesa = Mesa::find($id);
        return $mesa ? (object)$mesa->toArray() : null;
    }

    //Buscar mesas por provincia
    public function findByProvincia(int $provinciaId): array
    {
        return Mesa::where('provincia_id', $provinciaId)
            ->get()
            ->toArray();
    }

    //Insertar nueva mesa
    public function insert(array $data): int
    {
        $mesa = Mesa::create([
            'provincia_id' => $data['provincia_id'],
            'circuito' => $data['circuito'],
            'establecimiento' => $data['establecimiento'],
            'electores' => $data['electores'],
        ]);
        
        return $mesa->id;
    }

    //Actualizar mesa
    public function update(int $id, array $data): bool
    {
        return Mesa::where('id', $id)
            ->update([
                'provincia_id' => $data['provincia_id'],
                'circuito' => $data['circuito'],
                'establecimiento' => $data['establecimiento'],
                'electores' => $data['electores'],
            ]);
    }

    //Eliminar mesa
    public function delete(int $id): bool
    {
        return Mesa::destroy($id) > 0;
    }

    //Verificar si existe mesa en circuito
    public function existeMesaEnCircuito(int $provinciaId, string $circuito, string $establecimiento, ?int $excludeId = null): bool
    {
        $query = Mesa::where('provincia_id', $provinciaId)
            ->where('circuito', $circuito)
            ->where('establecimiento', $establecimiento);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    //Contar mesas
    public function count(): int
    {
        return Mesa::count();
    }

    //Contar mesas por provincia
    public function countByProvincia(int $provinciaId): int
    {
        return Mesa::where('provincia_id', $provinciaId)->count();
    }
}
