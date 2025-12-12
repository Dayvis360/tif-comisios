<?php

namespace App\DAO;

use App\Models\Telegrama;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * TelegramaDAO
 * 
 * Responsabilidad:
 * - Encapsular las consultas concretas a la base de datos
 * - Usar Query Builder o SQL directo
 * - Conocer nombres de tablas y columnas
 */
class TelegramaDAO
{
    /**
     * Obtener todos los telegramas como Collection con relaciones
     */
    public function obtenerTodos(): Collection
    {
        return Telegrama::with(['mesa.provincia', 'lista'])
            ->orderBy('mesa_id')
            ->orderBy('lista_id')
            ->get();
    }

    /**
     * Buscar telegrama por ID con relaciones
     */
    public function buscarPorId(int $id): ?Telegrama
    {
        return Telegrama::with(['mesa.provincia', 'lista'])->find($id);
    }

    /**
     * Buscar telegramas por mesa como Collection
     */
    public function buscarPorMesa(int $mesaId): Collection
    {
        return Telegrama::with('lista')
            ->where('mesa_id', $mesaId)
            ->get();
    }

    /**
     * Buscar telegramas por lista como Collection
     */
    public function buscarPorLista(int $listaId): Collection
    {
        return Telegrama::where('lista_id', $listaId)->get();
    }

    /**
     * Obtener todos los telegramas
     */
    public function getAll(): array
    {
        return DB::table('telegramas')
            ->join('mesas', 'telegramas.mesa_id', '=', 'mesas.id')
            ->join('provincias', 'mesas.provincia_id', '=', 'provincias.id')
            ->join('listas', 'telegramas.lista_id', '=', 'listas.id')
            ->select(
                'telegramas.*',
                'mesas.circuito as mesa_circuito',
                'mesas.establecimiento as mesa_establecimiento',
                'provincias.nombre as provincia_nombre',
                'listas.nombre as lista_nombre',
                'listas.cargo as lista_cargo'
            )
            ->orderBy('telegramas.mesa_id', 'asc')
            ->orderBy('telegramas.lista_id', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Buscar telegrama por ID
     */
    public function findById(int $id): ?object
    {
        return DB::table('telegramas')
            ->where('id', $id)
            ->first();
    }

    /**
     * Buscar telegramas por mesa
     */
    public function findByMesa(int $mesaId): array
    {
        return DB::table('telegramas')
            ->where('mesa_id', $mesaId)
            ->get()
            ->toArray();
    }

    /**
     * Buscar telegramas por lista
     */
    public function findByLista(int $listaId): array
    {
        return DB::table('telegramas')
            ->where('lista_id', $listaId)
            ->get()
            ->toArray();
    }

    /**
     * Insertar un nuevo telegrama
     */
    public function insert(array $data): int
    {
        return DB::table('telegramas')->insertGetId([
            'mesa_id' => $data['mesa_id'],
            'lista_id' => $data['lista_id'],
            'votos_Diputados' => $data['votos_Diputados'],
            'votos_Senadores' => $data['votos_Senadores'],
            'voto_Blancos' => $data['voto_Blancos'],
            'voto_Nulos' => $data['voto_Nulos'],
            'voto_Recurridos' => $data['voto_Recurridos'],
            'usuario_carga' => $data['usuario_carga'] ?? 'Sistema',
            'fecha_carga' => $data['fecha_carga'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Actualizar un telegrama existente
     */
    public function update(int $id, array $data): bool
    {
        return DB::table('telegramas')
            ->where('id', $id)
            ->update([
                'mesa_id' => $data['mesa_id'],
                'lista_id' => $data['lista_id'],
                'votos_Diputados' => $data['votos_Diputados'],
                'votos_Senadores' => $data['votos_Senadores'],
                'voto_Blancos' => $data['voto_Blancos'],
                'voto_Nulos' => $data['voto_Nulos'],
                'voto_Recurridos' => $data['voto_Recurridos'],
                'usuario_modificacion' => $data['usuario_modificacion'] ?? 'Sistema',
                'fecha_modificacion' => $data['fecha_modificacion'] ?? now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Eliminar un telegrama
     */
    public function delete(int $id): bool
    {
        return DB::table('telegramas')
            ->where('id', $id)
            ->delete();
    }

    /**
     * Verificar si existe un telegrama para una mesa y lista específica
     */
    public function existeTelegramaParaMesaYLista(int $mesaId, int $listaId, ?int $excludeId = null): bool
    {
        $query = DB::table('telegramas')
            ->where('mesa_id', $mesaId)
            ->where('lista_id', $listaId);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Obtener total de votos de una mesa
     */
    public function getTotalVotosMesa(int $mesaId): object
    {
        return DB::table('telegramas')
            ->where('mesa_id', $mesaId)
            ->selectRaw('
                SUM(votos_Diputados) as total_diputados,
                SUM(votos_Senadores) as total_senadores,
                SUM(voto_Blancos) as total_blancos,
                SUM(voto_Nulos) as total_nulos,
                SUM(voto_Recurridos) as total_recurridos
            ')
            ->first();
    }

    /**
     * Contar telegramas
     */
    public function count(): int
    {
        return DB::table('telegramas')->count();
    }

    /**
     * Insertar múltiples telegramas en lote (importación masiva)
     * Más eficiente que insertar uno por uno
     */
    public function insertBatch(array $telegramas): bool
    {
        // Preparar datos para inserción masiva
        $data = array_map(function($telegrama) {
            return [
                'mesa_id' => $telegrama['mesa_id'],
                'lista_id' => $telegrama['lista_id'],
                'votos_Diputados' => $telegrama['votos_Diputados'],
                'votos_Senadores' => $telegrama['votos_Senadores'],
                'voto_Blancos' => $telegrama['voto_Blancos'],
                'voto_Nulos' => $telegrama['voto_Nulos'],
                'voto_Recurridos' => $telegrama['voto_Recurridos'],
                'usuario_carga' => $telegrama['usuario_carga'] ?? 'Importación',
                'fecha_carga' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $telegramas);

        // Insertar todos los registros de una vez
        return DB::table('telegramas')->insert($data);
    }
}
