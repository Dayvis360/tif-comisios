<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

/**
 * CandidatoDAO
 * 
 * Responsabilidad:
 * - Encapsular las consultas concretas a la base de datos
 * - Usar Query Builder o SQL directo
 * - Conocer nombres de tablas y columnas
 */
class CandidatoDAO
{
    /**
     * Obtener todos los candidatos
     */
    public function getAll(): array
    {
        return DB::table('candidatos')
            ->join('listas', 'candidatos.lista_id', '=', 'listas.id')
            ->join('provincias', 'listas.provincia_id', '=', 'provincias.id')
            ->select(
                'candidatos.*',
                'listas.nombre as lista_nombre',
                'listas.cargo as lista_cargo',
                'provincias.nombre as provincia_nombre'
            )
            ->orderBy('candidatos.lista_id', 'asc')
            ->orderBy('candidatos.orden_en_lista', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Buscar candidato por ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('candidatos')
            ->where('id', $id)
            ->first();
    }

    /**
     * Buscar candidatos por lista
     */
    public function findByLista(int $listaId): array
    {
        return DB::table('candidatos')
            ->where('lista_id', $listaId)
            ->orderBy('orden_en_lista', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Insertar un nuevo candidato
     */
    public function insert(array $data): int
    {
        return DB::table('candidatos')->insertGetId([
            'nombre' => $data['nombre'],
            'orden_en_lista' => $data['orden_en_lista'],
            'lista_id' => $data['lista_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Actualizar un candidato existente
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('candidatos')
            ->where('id', $id)
            ->update([
                'nombre' => $data['nombre'],
                'orden_en_lista' => $data['orden_en_lista'],
                'lista_id' => $data['lista_id'],
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar un candidato
     */
    public function delete(int $id): bool
    {
        return DB::table('candidatos')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Verificar si existe un candidato con el mismo orden en una lista
     */
    public function existeOrdenEnLista(int $listaId, int $orden, ?int $excludeId = null): bool
    {
        $query = DB::table('candidatos')
            ->where('lista_id', $listaId)
            ->where('orden_en_lista', $orden);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obtener el máximo orden en una lista
     */
    public function getMaxOrdenEnLista(int $listaId): int
    {
        return DB::table('candidatos')
            ->where('lista_id', $listaId)
            ->max('orden_en_lista') ?? 0;
    }

    /**
     * Contar candidatos
     */
    public function count(): int
    {
        return DB::table('candidatos')->count();
    }

    /**
     * Contar candidatos por lista
     */
    public function countByLista(int $listaId): int
    {
        return DB::table('candidatos')
            ->where('lista_id', $listaId)
            ->count();
    }
}
