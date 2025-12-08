<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

/**
 * MesaDAO
 * 
 * Responsabilidad:
 * - Encapsular las consultas concretas a la base de datos
 * - Usar Query Builder o SQL directo
 * - Conocer nombres de tablas y columnas
 * - NO contiene lógica de negocio
 */
class MesaDAO
{
    /**
     * Obtener todas las mesas con información de provincia
     */
    public function getAll(): array
    {
        return DB::table('mesas')
            ->join('provincias', 'mesas.provincia_id', '=', 'provincias.id')
            ->select('mesas.*', 'provincias.nombre as provincia_nombre')
            ->orderBy('mesas.provincia_id', 'asc')
            ->orderBy('mesas.circuito', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Buscar mesa por ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('mesas')
            ->where('id', $id)
            ->first();
    }

    /**
     * Buscar mesas por provincia
     */
    public function findByProvincia(int $provinciaId): array
    {
        return DB::table('mesas')
            ->where('provincia_id', $provinciaId)
            ->get()
            ->toArray();
    }

    /**
     * Insertar una nueva mesa
     */
    public function insert(array $data): int
    {
        return DB::table('mesas')->insertGetId([
            'provincia_id' => $data['provincia_id'],
            'circuito' => $data['circuito'],
            'establecimiento' => $data['establecimiento'],
            'electores' => $data['electores'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Actualizar una mesa existente
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('mesas')
            ->where('id', $id)
            ->update([
                'provincia_id' => $data['provincia_id'],
                'circuito' => $data['circuito'],
                'establecimiento' => $data['establecimiento'],
                'electores' => $data['electores'],
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar una mesa
     */
    public function delete(int $id): bool
    {
        return DB::table('mesas')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Verificar si existe una mesa con el mismo circuito y establecimiento en una provincia
     */
    public function existeMesaEnCircuito(int $provinciaId, string $circuito, string $establecimiento, ?int $excludeId = null): bool
    {
        $query = DB::table('mesas')
            ->where('provincia_id', $provinciaId)
            ->where('circuito', $circuito)
            ->where('establecimiento', $establecimiento);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Contar mesas
     */
    public function count(): int
    {
        return DB::table('mesas')->count();
    }

    /**
     * Contar mesas por provincia
     */
    public function countByProvincia(int $provinciaId): int
    {
        return DB::table('mesas')
            ->where('provincia_id', $provinciaId)
            ->count();
    }
}
