<?php

namespace App\Services;

use App\Repositories\TelegramaRepository;
use App\Repositories\ListaRepository;
use App\Repositories\ProvinciaRepository;

/**
 * DHontService - Servicio para cálculo de reparto de bancas usando método D'Hont
 * 
 * Responsabilidad:
 * - Implementar el algoritmo D'Hont para distribución proporcional de escaños
 * - Calcular votos totales por lista en una provincia
 * - Retornar distribución de bancas por lista
 * 
 * Algoritmo D'Hont:
 * 1. Se dividen los votos de cada lista por 1, 2, 3, 4, ... n
 * 2. Se ordenan todos los cocientes de mayor a menor
 * 3. Se asignan las bancas a las listas según los cocientes más altos
 */
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

    /**
     * Calcular reparto de bancas para una provincia usando método D'Hont
     * 
     * RESPONSABILIDAD DEL SERVICIO:
     * - Orquestar el caso de uso "calcular distribución de bancas"
     * - Delegar la lógica de negocio al Modelo Provincia
     * - Formatear la respuesta para el controlador
     * 
     * @param int $provinciaId ID de la provincia
     * @param string $cargo Tipo de cargo: 'DIPUTADOS' o 'SENADORES'
     * @return array Array con distribución de bancas
     * @throws \Exception Si la provincia no existe o no tiene bancas definidas
     */
    public function calcularRepartoBancas(int $provinciaId, string $cargo = 'DIPUTADOS'): array
    {
        // Verificar que el cargo sea válido
        $cargo = strtoupper(trim($cargo));
        if (!in_array($cargo, ['DIPUTADOS', 'SENADORES'])) {
            throw new \InvalidArgumentException("El cargo debe ser DIPUTADOS o SENADORES");
        }

        // Obtener la provincia (con sus relaciones)
        $provincia = $this->provinciaRepository->obtenerPorId($provinciaId);
        if (!$provincia) {
            throw new \Exception("La provincia con ID {$provinciaId} no existe");
        }

        // Verificar que pueda distribuir (LÓGICA DE NEGOCIO EN EL MODELO)
        if (!$provincia->puedeDistribuirBancas($cargo)) {
            throw new \Exception("La provincia '{$provincia->nombre}' no tiene bancas definidas para {$cargo}");
        }

        // DELEGAR LÓGICA DE NEGOCIO AL MODELO
        $distribucion = $provincia->distribuirBancasDHont($cargo);

        // Obtener bancas según el cargo
        $bancasTotales = $cargo === 'SENADORES' ? $provincia->bancas_senadores : $provincia->bancas_diputados;

        // Formatear respuesta para el controlador
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

    /**
     * Obtener votos totales por lista en una provincia para un cargo específico
     * 
     * @param int $provinciaId
     * @param string $cargo
     * @return array Array de listas con sus votos totales
     */
    private function obtenerVotosPorLista(int $provinciaId, string $cargo): array
    {
        // Obtener todas las listas de la provincia para ese cargo
        $listas = $this->listaRepository->obtenerPorProvinciaYCargo($provinciaId, $cargo);

        $votosPorLista = [];

        foreach ($listas as $lista) {
            // Sumar votos de todos los telegramas de esta lista
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

    /**
     * Calcular votos totales de una lista según el cargo
     * 
     * @param int $listaId
     * @param string $cargo
     * @return int Total de votos
     */
    private function calcularVotosTotalesLista(int $listaId, string $cargo): int
    {
        // Obtener todos los telegramas de la lista
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

    /**
     * Aplicar el algoritmo D'Hont para distribuir bancas
     * 
     * @param array $votosPorLista Array con votos de cada lista
     * @param int $bancasDisponibles Número de bancas a distribuir
     * @return array Distribución de bancas con detalles del cálculo
     */
    private function aplicarMetodoDHont(array $votosPorLista, int $bancasDisponibles): array
    {
        // Inicializar bancas asignadas
        $bancasAsignadas = [];
        foreach ($votosPorLista as $lista) {
            $bancasAsignadas[$lista['lista_id']] = 0;
        }

        // Crear array de cocientes para el algoritmo
        $cocientes = [];

        // Asignar las bancas una por una
        for ($banca = 0; $banca < $bancasDisponibles; $banca++) {
            $maxCociente = 0;
            $listaGanadora = null;

            // Calcular cocientes actuales para cada lista
            foreach ($votosPorLista as $lista) {
                $listaId = $lista['lista_id'];
                $votos = $lista['votos'];
                
                // D'Hont: dividir votos por (bancas ya asignadas + 1)
                $divisor = $bancasAsignadas[$listaId] + 1;
                $cociente = $votos / $divisor;

                // Buscar el cociente máximo
                if ($cociente > $maxCociente) {
                    $maxCociente = $cociente;
                    $listaGanadora = $listaId;
                }
            }

            // Asignar la banca a la lista con el cociente más alto
            if ($listaGanadora !== null) {
                $bancasAsignadas[$listaGanadora]++;
            }
        }

        // Construir resultado con detalles
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

        // Ordenar por bancas asignadas (mayor a menor)
        usort($resultado, function($a, $b) {
            if ($b['bancas_asignadas'] === $a['bancas_asignadas']) {
                return $b['votos'] <=> $a['votos']; // Desempate por votos
            }
            return $b['bancas_asignadas'] <=> $a['bancas_asignadas'];
        });

        return $resultado;
    }

    /**
     * Calcular reparto para todas las provincias
     * 
     * @param string $cargo Tipo de cargo
     * @return array Array con distribución de todas las provincias
     */
    public function calcularRepartoTodasProvincias(string $cargo = 'DIPUTADOS'): array
    {
        $provincias = $this->provinciaRepository->obtenerTodas();
        $resultados = [];

        foreach ($provincias as $provincia) {
            try {
                $resultado = $this->calcularRepartoBancas($provincia->id, $cargo);
                $resultados[] = $resultado;
            } catch (\Exception $e) {
                // Registrar provincias con errores
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
