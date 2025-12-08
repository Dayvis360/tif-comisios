<?php

namespace App\Repositories;

use App\DAO\ResultadoDAO;

/**
 * ResultadoRepository
 * 
 * Responsabilidad:
 * - Ofrecer métodos de acceso a datos para resultados electorales
 * - Transformar datos crudos del DAO a formato útil para el Service
 */
class ResultadoRepository
{
    private ResultadoDAO $resultadoDAO;

    public function __construct(ResultadoDAO $resultadoDAO)
    {
        $this->resultadoDAO = $resultadoDAO;
    }

    /**
     * Obtener totales nacionales por lista
     */
    public function obtenerTotalesNacionales(): array
    {
        return $this->resultadoDAO->getTotalesNacionales();
    }

    /**
     * Obtener participación electoral nacional
     */
    public function obtenerParticipacionNacional(): object
    {
        return $this->resultadoDAO->getParticipacionNacional();
    }

    /**
     * Obtener totales por provincia
     */
    public function obtenerTotalesPorProvincia(int $provinciaId): array
    {
        return $this->resultadoDAO->getTotalesPorProvincia($provinciaId);
    }

    /**
     * Obtener participación por provincia
     */
    public function obtenerParticipacionPorProvincia(int $provinciaId): object
    {
        return $this->resultadoDAO->getParticipacionPorProvincia($provinciaId);
    }

    /**
     * Obtener ranking nacional por cargo
     */
    public function obtenerRankingNacional(string $cargo): array
    {
        return $this->resultadoDAO->getRankingNacional($cargo);
    }

    /**
     * Obtener votos válidos vs inválidos por provincia
     */
    public function obtenerVotosValidosInvalidos(int $provinciaId): object
    {
        return $this->resultadoDAO->getVotosValidosInvalidos($provinciaId);
    }

    /**
     * Obtener resumen de todas las provincias
     */
    public function obtenerResumenTodasProvincias(): array
    {
        return $this->resultadoDAO->getResumenTodasProvincias();
    }
}
