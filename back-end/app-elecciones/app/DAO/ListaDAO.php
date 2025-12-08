<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

/**
 * ListaDAO
 * 
 * Responsabilidad:
 * - Encapsular las consultas concretas a la base de datos
 * - Usar Query Builder o SQL directo
 * - Conocer nombres de tablas y columnas
 */
class ListaDAO
{
    /**
     * Obtener todas las listas
     */
    public function getAll(): array
    {
        return DB::table('listas')
            ->join('provincias', 'listas.provincia_id', '=', 'provincias.id')
            ->select('listas.*', 'provincias.nombre as provincia_nombre')
            ->orderBy('listas.provincia_id', 'asc')
            ->orderBy('listas.nombre', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Buscar lista por ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('listas')
            ->where('id', $id)
            ->first();
    }

    /**
     * Buscar listas por provincia
     */
    public function findByProvincia(int $provinciaId): array
    {
        return DB::table('listas')
            ->where('provincia_id', $provinciaId)
            ->get()
            ->toArray();
    }

    /**
     * Buscar listas por cargo
     */
    public function findByCargo(string $cargo): array
    {
        return DB::table('listas')
            ->where('cargo', $cargo)
            ->get()
            ->toArray();
    }

    /**
     * Insertar una nueva lista
     */
    public function insert(array $data): int
    {
        return DB::table('listas')->insertGetId([
            'nombre' => $data['nombre'],
            'alianza' => $data['alianza'] ?? null,
            'cargo' => $data['cargo'],
            'provincia_id' => $data['provincia_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Actualizar una lista existente
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('listas')
            ->where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'alianza' => $data['alianza'] ?? null,
                'cargo' => $data['cargo'],
                'provincia_id' => $data['provincia_id'],
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar una lista
     */
    public function delete(int $id): bool
    {
        return DB::table('listas')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Verificar si existe una lista con el mismo nombre en una provincia y cargo
     */
    public function existeListaEnProvincia(string $nombre, int $provinciaId, string $cargo, ?int $excludeId = null): bool
    {
        $query = DB::table('listas')
            ->where('nombre', $nombre)
            ->where('provincia_id', $provinciaId)
            ->where('cargo', $cargo);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Contar listas
     */
    public function count(): int
    {
        return DB::table('listas')->count();
    }
}
