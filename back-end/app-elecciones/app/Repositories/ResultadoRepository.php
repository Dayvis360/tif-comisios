<?php

namespace App\Repositories;

use App\DAO\ResultadoDAO;

//Repositorio de acceso a datos de resultados electorales
class ResultadoRepository
{
    private ResultadoDAO $resultadoDAO;

    public function __construct(ResultadoDAO $resultadoDAO)
    {
        $this->resultadoDAO = $resultadoDAO;
    }

    public function obtenerTotalesNacionales(): array
    {
        return $this->resultadoDAO->getTotalesNacionales();
    }

    public function obtenerParticipacionNacional(): object
    {
        return $this->resultadoDAO->getParticipacionNacional();
    }

    public function obtenerTotalesPorProvincia(int $provinciaId): array
    {
        return $this->resultadoDAO->getTotalesPorProvincia($provinciaId);
    }

    public function obtenerParticipacionPorProvincia(int $provinciaId): object
    {
        return $this->resultadoDAO->getParticipacionPorProvincia($provinciaId);
    }

    public function obtenerRankingNacional(string $cargo): array
    {
        return $this->resultadoDAO->getRankingNacional($cargo);
    }

    public function obtenerVotosValidosInvalidos(int $provinciaId): object
    {
        return $this->resultadoDAO->getVotosValidosInvalidos($provinciaId);
    }

    public function obtenerResumenTodasProvincias(): array
    {
        return $this->resultadoDAO->getResumenTodasProvincias();
    }
}
