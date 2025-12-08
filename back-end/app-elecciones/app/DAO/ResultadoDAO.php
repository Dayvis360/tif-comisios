<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

/**
 * ResultadoDAO
 * 
 * Responsabilidad:
 * - Encapsular consultas SQL complejas para cálculos de resultados
 * - Agregaciones nacionales y provinciales
 * - Estadísticas electorales
 */
class ResultadoDAO
{
    /**
     * Obtener totales nacionales por lista y cargo
     */
    public function getTotalesNacionales(): array
    {
        return DB::select("
            SELECT 
                l.id as lista_id,
                l.nombre as lista_nombre,
                l.cargo,
                SUM(t.votos_Diputados) as total_votos_diputados,
                SUM(t.votos_Senadores) as total_votos_senadores,
                COUNT(DISTINCT t.mesa_id) as total_mesas
            FROM listas l
            LEFT JOIN telegramas t ON l.id = t.lista_id
            GROUP BY l.id, l.nombre, l.cargo
            ORDER BY l.cargo, total_votos_diputados DESC
        ");
    }

    /**
     * Obtener participación electoral nacional
     */
    public function getParticipacionNacional(): object
    {
        $result = DB::selectOne("
            SELECT 
                SUM(m.electores) as total_electores,
                SUM(t.votos_Diputados + t.votos_Senadores + t.voto_Blancos + t.voto_Nulos + t.voto_Recurridos) as total_votos_emitidos,
                COUNT(DISTINCT m.id) as total_mesas
            FROM mesas m
            LEFT JOIN telegramas t ON m.id = t.mesa_id
        ");

        return $result ?: (object)[
            'total_electores' => 0,
            'total_votos_emitidos' => 0,
            'total_mesas' => 0
        ];
    }

    /**
     * Obtener totales por provincia
     */
    public function getTotalesPorProvincia(int $provinciaId): array
    {
        return DB::select("
            SELECT 
                l.id as lista_id,
                l.nombre as lista_nombre,
                l.cargo,
                SUM(t.votos_Diputados) as total_votos_diputados,
                SUM(t.votos_Senadores) as total_votos_senadores,
                SUM(t.voto_Blancos) as total_blancos,
                SUM(t.voto_Nulos) as total_nulos,
                SUM(t.voto_Recurridos) as total_recurridos,
                COUNT(t.id) as total_telegramas
            FROM listas l
            LEFT JOIN telegramas t ON l.id = t.lista_id
            LEFT JOIN mesas m ON t.mesa_id = m.id
            WHERE m.provincia_id = ?
            GROUP BY l.id, l.nombre, l.cargo
            ORDER BY l.cargo, total_votos_diputados DESC
        ", [$provinciaId]);
    }

    /**
     * Obtener participación por provincia
     */
    public function getParticipacionPorProvincia(int $provinciaId): object
    {
        $result = DB::selectOne("
            SELECT 
                p.id as provincia_id,
                p.nombre as provincia_nombre,
                SUM(m.electores) as total_electores,
                COUNT(DISTINCT m.id) as total_mesas,
                COUNT(DISTINCT t.id) as total_telegramas,
                SUM(t.votos_Diputados) as total_votos_diputados,
                SUM(t.votos_Senadores) as total_votos_senadores,
                SUM(t.voto_Blancos) as total_blancos,
                SUM(t.voto_Nulos) as total_nulos,
                SUM(t.voto_Recurridos) as total_recurridos
            FROM provincias p
            LEFT JOIN mesas m ON p.id = m.provincia_id
            LEFT JOIN telegramas t ON m.id = t.mesa_id
            WHERE p.id = ?
            GROUP BY p.id, p.nombre
        ", [$provinciaId]);

        return $result ?: (object)[
            'provincia_id' => $provinciaId,
            'provincia_nombre' => '',
            'total_electores' => 0,
            'total_mesas' => 0,
            'total_telegramas' => 0,
            'total_votos_diputados' => 0,
            'total_votos_senadores' => 0,
            'total_blancos' => 0,
            'total_nulos' => 0,
            'total_recurridos' => 0
        ];
    }

    /**
     * Obtener ranking nacional de listas por cargo
     */
    public function getRankingNacional(string $cargo): array
    {
        return DB::select("
            SELECT 
                l.id as lista_id,
                l.nombre as lista_nombre,
                l.cargo,
                SUM(CASE WHEN l.cargo = 'DIPUTADOS' THEN t.votos_Diputados ELSE 0 END) as total_votos_diputados,
                SUM(CASE WHEN l.cargo = 'SENADORES' THEN t.votos_Senadores ELSE 0 END) as total_votos_senadores,
                COUNT(DISTINCT t.mesa_id) as mesas_con_votos
            FROM listas l
            LEFT JOIN telegramas t ON l.id = t.lista_id
            WHERE l.cargo = ?
            GROUP BY l.id, l.nombre, l.cargo
            ORDER BY 
                CASE 
                    WHEN l.cargo = 'DIPUTADOS' THEN SUM(t.votos_Diputados)
                    ELSE SUM(t.votos_Senadores)
                END DESC
        ", [$cargo]);
    }

    /**
     * Obtener votos válidos vs inválidos por provincia
     */
    public function getVotosValidosInvalidos(int $provinciaId): object
    {
        $result = DB::selectOne("
            SELECT 
                SUM(t.votos_Diputados + t.votos_Senadores) as votos_validos,
                SUM(t.voto_Blancos) as votos_blancos,
                SUM(t.voto_Nulos) as votos_nulos,
                SUM(t.voto_Recurridos) as votos_recurridos
            FROM telegramas t
            INNER JOIN mesas m ON t.mesa_id = m.id
            WHERE m.provincia_id = ?
        ", [$provinciaId]);

        return $result ?: (object)[
            'votos_validos' => 0,
            'votos_blancos' => 0,
            'votos_nulos' => 0,
            'votos_recurridos' => 0
        ];
    }

    /**
     * Obtener todas las provincias con sus totales
     */
    public function getResumenTodasProvincias(): array
    {
        return DB::select("
            SELECT 
                p.id as provincia_id,
                p.nombre as provincia_nombre,
                p.bancas_diputados,
                p.bancas_senadores,
                COUNT(DISTINCT m.id) as total_mesas,
                SUM(m.electores) as total_electores,
                COUNT(DISTINCT t.id) as total_telegramas,
                SUM(t.votos_Diputados) as total_votos_diputados,
                SUM(t.votos_Senadores) as total_votos_senadores
            FROM provincias p
            LEFT JOIN mesas m ON p.id = m.provincia_id
            LEFT JOIN telegramas t ON m.id = t.mesa_id
            GROUP BY p.id, p.nombre, p.bancas_diputados, p.bancas_senadores
            ORDER BY p.nombre
        ");
    }
}
