<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

/**
 * ProvinciaDAO
 * 
 * Responsabilidad:
 * - Encapsular las consultas concretas a la base de datos
 * - Usar Query Builder o SQL directo
 * - Conocer nombres de tablas y columnas
 * - NO contiene lógica de negocio
 */
class ProvinciaDAO
{
    /**
     * Obtener todas las provincias
     */
    public function getAll(): array
    {
        return DB::table('provincias')
            ->orderBy('nombre', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Buscar provincia por ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('provincias')
            ->where('id', $id)
            ->first();
    }

    /**
     * Buscar provincia por nombre
     */
    public function findByNombre(string $nombre): ?object
    {
        return DB::table('provincias')
            ->where('nombre', $nombre)
            ->first();
    }

    /**
     * Insertar una nueva provincia
     */
    public function insert(array $data): int
    {
        return DB::table('provincias')->insertGetId([
            'nombre' => $data['nombre'],
            'bancas_diputados' => $data['bancas_diputados'] ?? null,
            'bancas_senadores' => $data['bancas_senadores'] ?? 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Actualizar una provincia existente
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('provincias')
            ->where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'bancas_diputados' => $data['bancas_diputados'] ?? null,
                'bancas_senadores' => $data['bancas_senadores'] ?? 3,
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar una provincia
     */
    public function delete(int $id): bool
    {
        return DB::table('provincias')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Verificar si existe una provincia con el nombre dado (excluyendo un ID específico)
     */
    public function existeNombre(string $nombre, ?int $excludeId = null): bool
    {
        $query = DB::table('provincias')
            ->where('nombre', $nombre);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Contar provincias
     */
    public function count(): int
    {
        return DB::table('provincias')->count();
    }
}
