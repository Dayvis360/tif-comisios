<?php

namespace App\Services;

use App\Repositories\TelegramaRepository;
use App\Repositories\ListaRepository;
use App\Repositories\ProvinciaRepository;

//Servicio para cálculo de reparto de bancas usando método D'Hont
class DHontService
{
    private TelegramaRepository $telegramaRepository;
    private ListaRepository $listaRepository;
    private ProvinciaRepository $provinciaRepository;

    public function __construct(
        TelegramaRepository $telegramaRepository,
        ListaRepository $listaRepository,
        ProvinciaRepository $provinciaRepository
    ) {
        $this->telegramaRepository = $telegramaRepository;
        $this->listaRepository = $listaRepository;
        $this->provinciaRepository = $provinciaRepository;
    }

    //Calcular reparto de bancas para una provincia
    public function calcularRepartoBancas(int $provinciaId, string $cargo = 'DIPUTADOS'): array
    {
        $cargo = strtoupper(trim($cargo));
        if (!in_array($cargo, ['DIPUTADOS', 'SENADORES'])) {
            throw new \InvalidArgumentException("El cargo debe ser DIPUTADOS o SENADORES");
        }

        $provincia = $this->provinciaRepository->obtenerPorId($provinciaId);
        if (!$provincia) {
            throw new \Exception("La provincia con ID {$provinciaId} no existe");
        }

        if (!$provincia->puedeDistribuirBancas($cargo)) {
            throw new \Exception("La provincia '{$provincia->nombre}' no tiene bancas definidas para {$cargo}");
        }

        $distribucion = $provincia->distribuirBancasDHont($cargo);
        $bancasTotales = $cargo === 'SENADORES' ? $provincia->bancas_senadores : $provincia->bancas_diputados;
        return [
            'provincia' => $provincia->nombre,
            'provincia_id' => $provinciaId,
            'cargo' => $cargo,
            'bancas_totales' => $bancasTotales,
            'votos_totales' => $distribucion['votos_totales'] ?? 0,
            'listas' => $distribucion['listas'],
            'mensaje' => $distribucion['mensaje'] ?? null,
        ];
    }

    //Obtener votos totales por lista en una provincia
    private function obtenerVotosPorLista(int $provinciaId, string $cargo): array
    {
        $listas = $this->listaRepository->obtenerPorProvinciaYCargo($provinciaId, $cargo);

        $votosPorLista = [];

        foreach ($listas as $lista) {
            $votos = $this->calcularVotosTotalesLista($lista->id, $cargo);

            if ($votos > 0) {
                $votosPorLista[] = [
                    'lista_id' => $lista->id,
                    'nombre' => $lista->nombre,
                    'alianza' => $lista->alianza,
                    'votos' => $votos,
                ];
            }
        }

        return $votosPorLista;
    }

    //Calcular votos totales de una lista
    private function calcularVotosTotalesLista(int $listaId, string $cargo): int
    {
        $telegramas = $this->telegramaRepository->obtenerPorLista($listaId);

        $votosTotal = 0;

        foreach ($telegramas as $telegrama) {
            if ($cargo === 'DIPUTADOS') {
                $votosTotal += $telegrama->votos_Diputados;
            } else {
                $votosTotal += $telegrama->votos_Senadores;
            }
        }

        return $votosTotal;
    }

    //Aplicar el algoritmo D'Hont para distribuir bancas
    private function aplicarMetodoDHont(array $votosPorLista, int $bancasDisponibles): array
    {
        $bancasAsignadas = [];
        foreach ($votosPorLista as $lista) {
            $bancasAsignadas[$lista['lista_id']] = 0;
        }

        $cocientes = [];

        for ($banca = 0; $banca < $bancasDisponibles; $banca++) {
            $maxCociente = 0;
            $listaGanadora = null;

            foreach ($votosPorLista as $lista) {
                $listaId = $lista['lista_id'];
                $votos = $lista['votos'];
                
                $divisor = $bancasAsignadas[$listaId] + 1;
                $cociente = $votos / $divisor;

                if ($cociente > $maxCociente) {
                    $maxCociente = $cociente;
                    $listaGanadora = $listaId;
                }
            }

            if ($listaGanadora !== null) {
                $bancasAsignadas[$listaGanadora]++;
            }
        }

        $resultado = [];
        foreach ($votosPorLista as $lista) {
            $listaId = $lista['lista_id'];
            $bancas = $bancasAsignadas[$listaId];
            
            $resultado[] = [
                'lista_id' => $listaId,
                'nombre' => $lista['nombre'],
                'alianza' => $lista['alianza'],
                'votos' => $lista['votos'],
                'bancas_asignadas' => $bancas,
                'porcentaje_votos' => round(($lista['votos'] / array_sum(array_column($votosPorLista, 'votos'))) * 100, 2),
                'porcentaje_bancas' => $bancasDisponibles > 0 ? round(($bancas / $bancasDisponibles) * 100, 2) : 0,
            ];
        }

        usort($resultado, function($a, $b) {
            if ($b['bancas_asignadas'] === $a['bancas_asignadas']) {
                return $b['votos'] <=> $a['votos'];
            }
            return $b['bancas_asignadas'] <=> $a['bancas_asignadas'];
        });

        return $resultado;
    }

    //Calcular reparto para todas las provincias
    public function calcularRepartoTodasProvincias(string $cargo = 'DIPUTADOS'): array
    {
        $provincias = $this->provinciaRepository->obtenerTodas();
        $resultados = [];

        foreach ($provincias as $provincia) {
            try {
                $resultado = $this->calcularRepartoBancas($provincia->id, $cargo);
                $resultados[] = $resultado;
            } catch (\Exception $e) {
                $resultados[] = [
                    'provincia' => $provincia->nombre,
                    'provincia_id' => $provincia->id,
                    'cargo' => $cargo,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'cargo' => $cargo,
            'total_provincias' => count($provincias),
            'provincias' => $resultados,
        ];
    }
}
