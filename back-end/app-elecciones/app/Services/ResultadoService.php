<?php

namespace App\Services;

use App\Repositories\ResultadoRepository;

/**
 * ResultadoService
 * 
 * Responsabilidad:
 * - Orquestar casos de uso de resultados electorales
 * - Calcular estadísticas y agregaciones
 * - Preparar datos para presentación
 */
class ResultadoService
{
    private ResultadoRepository $resultadoRepository;

    public function __construct(ResultadoRepository $resultadoRepository)
    {
        $this->resultadoRepository = $resultadoRepository;
    }

    /**
     * Caso de uso: Obtener resultados nacionales completos
     * 
     * Incluye:
     * - Totales por lista (diputados y senadores)
     * - Ranking nacional
     * - Participación electoral
     * - Votos válidos vs inválidos
     */
    public function obtenerResultadosNacionales(): array
    {
        // 1. Obtener totales por lista
        $totales = $this->resultadoRepository->obtenerTotalesNacionales();

        // 2. Obtener participación
        $participacion = $this->resultadoRepository->obtenerParticipacionNacional();

        // 3. Separar por cargo
        $diputados = [];
        $senadores = [];

        foreach ($totales as $lista) {
            if ($lista->cargo === 'DIPUTADOS') {
                $diputados[] = [
                    'lista_id' => $lista->lista_id,
                    'lista_nombre' => $lista->lista_nombre,
                    'total_votos' => (int) $lista->total_votos_diputados,
                    'total_mesas' => (int) $lista->total_mesas
                ];
            } elseif ($lista->cargo === 'SENADORES') {
                $senadores[] = [
                    'lista_id' => $lista->lista_id,
                    'lista_nombre' => $lista->lista_nombre,
                    'total_votos' => (int) $lista->total_votos_senadores,
                    'total_mesas' => (int) $lista->total_mesas
                ];
            }
        }

        // 4. Calcular totales generales
        $totalVotosDiputados = array_sum(array_column($diputados, 'total_votos'));
        $totalVotosSenadores = array_sum(array_column($senadores, 'total_votos'));

        // 5. Calcular porcentaje de participación
        $totalElectores = (int) $participacion->total_electores;
        $totalVotosEmitidos = (int) $participacion->total_votos_emitidos;
        $porcentajeParticipacion = $totalElectores > 0 
            ? round(($totalVotosEmitidos / $totalElectores) * 100, 2) 
            : 0;

        // 6. Agregar porcentajes a cada lista
        foreach ($diputados as &$lista) {
            $lista['porcentaje'] = $totalVotosDiputados > 0 
                ? round(($lista['total_votos'] / $totalVotosDiputados) * 100, 2) 
                : 0;
        }

        foreach ($senadores as &$lista) {
            $lista['porcentaje'] = $totalVotosSenadores > 0 
                ? round(($lista['total_votos'] / $totalVotosSenadores) * 100, 2) 
                : 0;
        }

        return [
            'participacion' => [
                'total_electores' => $totalElectores,
                'total_votos_emitidos' => $totalVotosEmitidos,
                'porcentaje_participacion' => $porcentajeParticipacion,
                'total_mesas' => (int) $participacion->total_mesas
            ],
            'diputados' => [
                'total_votos' => $totalVotosDiputados,
                'listas' => $diputados
            ],
            'senadores' => [
                'total_votos' => $totalVotosSenadores,
                'listas' => $senadores
            ]
        ];
    }

    /**
     * Caso de uso: Obtener ranking nacional por cargo
     */
    public function obtenerRankingNacional(string $cargo): array
    {
        // Validar cargo
        if (!in_array($cargo, ['DIPUTADOS', 'SENADORES'])) {
            throw new \InvalidArgumentException("Cargo inválido. Debe ser DIPUTADOS o SENADORES");
        }

        // Obtener ranking
        $ranking = $this->resultadoRepository->obtenerRankingNacional($cargo);

        // Formatear respuesta
        $resultado = [];
        $posicion = 1;
        $totalVotos = 0;

        // Calcular total de votos
        foreach ($ranking as $lista) {
            $votos = $cargo === 'DIPUTADOS' 
                ? (int) $lista->total_votos_diputados 
                : (int) $lista->total_votos_senadores;
            $totalVotos += $votos;
        }

        // Agregar posición y porcentaje
        foreach ($ranking as $lista) {
            $votos = $cargo === 'DIPUTADOS' 
                ? (int) $lista->total_votos_diputados 
                : (int) $lista->total_votos_senadores;

            $porcentaje = $totalVotos > 0 
                ? round(($votos / $totalVotos) * 100, 2) 
                : 0;

            $resultado[] = [
                'posicion' => $posicion++,
                'lista_id' => $lista->lista_id,
                'lista_nombre' => $lista->lista_nombre,
                'total_votos' => $votos,
                'porcentaje' => $porcentaje,
                'mesas_con_votos' => (int) $lista->mesas_con_votos
            ];
        }

        return [
            'cargo' => $cargo,
            'total_votos' => $totalVotos,
            'ranking' => $resultado
        ];
    }

    /**
     * Caso de uso: Obtener estadísticas de una provincia
     */
    public function obtenerEstadisticasProvincia(int $provinciaId): array
    {
        // 1. Obtener totales por lista
        $totales = $this->resultadoRepository->obtenerTotalesPorProvincia($provinciaId);

        // 2. Obtener participación
        $participacion = $this->resultadoRepository->obtenerParticipacionPorProvincia($provinciaId);

        // 3. Obtener votos válidos vs inválidos
        $votosValidosInvalidos = $this->resultadoRepository->obtenerVotosValidosInvalidos($provinciaId);

        // 4. Separar por cargo
        $diputados = [];
        $senadores = [];

        foreach ($totales as $lista) {
            if ($lista->cargo === 'DIPUTADOS') {
                $diputados[] = [
                    'lista_id' => $lista->lista_id,
                    'lista_nombre' => $lista->lista_nombre,
                    'total_votos' => (int) $lista->total_votos_diputados,
                    'total_telegramas' => (int) $lista->total_telegramas
                ];
            } elseif ($lista->cargo === 'SENADORES') {
                $senadores[] = [
                    'lista_id' => $lista->lista_id,
                    'lista_nombre' => $lista->lista_nombre,
                    'total_votos' => (int) $lista->total_votos_senadores,
                    'total_telegramas' => (int) $lista->total_telegramas
                ];
            }
        }

        // 5. Calcular totales
        $totalElectores = (int) $participacion->total_electores;
        $totalVotosEmitidos = (int) ($participacion->total_votos_diputados + 
                                     $participacion->total_votos_senadores + 
                                     $participacion->total_blancos + 
                                     $participacion->total_nulos + 
                                     $participacion->total_recurridos);

        $votosValidos = (int) $votosValidosInvalidos->votos_validos;
        $votosBlancos = (int) $votosValidosInvalidos->votos_blancos;
        $votosNulos = (int) $votosValidosInvalidos->votos_nulos;
        $votosRecurridos = (int) $votosValidosInvalidos->votos_recurridos;
        $votosInvalidos = $votosBlancos + $votosNulos + $votosRecurridos;

        // 6. Calcular porcentajes
        $porcentajeParticipacion = $totalElectores > 0 
            ? round(($totalVotosEmitidos / $totalElectores) * 100, 2) 
            : 0;

        $porcentajeValidos = $totalVotosEmitidos > 0 
            ? round(($votosValidos / $totalVotosEmitidos) * 100, 2) 
            : 0;

        $porcentajeInvalidos = $totalVotosEmitidos > 0 
            ? round(($votosInvalidos / $totalVotosEmitidos) * 100, 2) 
            : 0;

        return [
            'provincia' => [
                'id' => (int) $participacion->provincia_id,
                'nombre' => $participacion->provincia_nombre,
                'total_mesas' => (int) $participacion->total_mesas,
                'total_electores' => $totalElectores
            ],
            'participacion' => [
                'total_votos_emitidos' => $totalVotosEmitidos,
                'porcentaje_participacion' => $porcentajeParticipacion
            ],
            'votos_validos_invalidos' => [
                'votos_validos' => $votosValidos,
                'votos_invalidos' => $votosInvalidos,
                'votos_blancos' => $votosBlancos,
                'votos_nulos' => $votosNulos,
                'votos_recurridos' => $votosRecurridos,
                'porcentaje_validos' => $porcentajeValidos,
                'porcentaje_invalidos' => $porcentajeInvalidos
            ],
            'resultados_diputados' => $diputados,
            'resultados_senadores' => $senadores
        ];
    }

    /**
     * Caso de uso: Obtener resumen de todas las provincias
     */
    public function obtenerResumenProvincias(): array
    {
        $provincias = $this->resultadoRepository->obtenerResumenTodasProvincias();

        $resultado = [];
        foreach ($provincias as $provincia) {
            $totalVotos = (int) ($provincia->total_votos_diputados + $provincia->total_votos_senadores);
            $totalElectores = (int) $provincia->total_electores;
            
            $porcentajeParticipacion = $totalElectores > 0 
                ? round(($totalVotos / $totalElectores) * 100, 2) 
                : 0;

            $resultado[] = [
                'provincia_id' => $provincia->provincia_id,
                'provincia_nombre' => $provincia->provincia_nombre,
                'bancas_diputados' => (int) $provincia->bancas_diputados,
                'bancas_senadores' => (int) $provincia->bancas_senadores,
                'total_mesas' => (int) $provincia->total_mesas,
                'total_electores' => $totalElectores,
                'total_telegramas' => (int) $provincia->total_telegramas,
                'total_votos' => $totalVotos,
                'porcentaje_participacion' => $porcentajeParticipacion
            ];
        }

        return $resultado;
    }

    /**
     * Caso de uso: Exportar resultados provinciales a CSV
     */
    public function exportarResultadosProvinciaCSV(int $provinciaId): string
    {
        $estadisticas = $this->obtenerEstadisticasProvincia($provinciaId);

        // Crear archivo CSV en memoria
        $output = fopen('php://temp', 'r+');

        // Encabezados generales
        fputcsv($output, ['RESULTADOS ELECTORALES - ' . strtoupper($estadisticas['provincia']['nombre'])]);
        fputcsv($output, []);

        // Información de la provincia
        fputcsv($output, ['Información General']);
        fputcsv($output, ['Total Mesas', $estadisticas['provincia']['total_mesas']]);
        fputcsv($output, ['Total Electores', $estadisticas['provincia']['total_electores']]);
        fputcsv($output, ['Votos Emitidos', $estadisticas['participacion']['total_votos_emitidos']]);
        fputcsv($output, ['Participación (%)', $estadisticas['participacion']['porcentaje_participacion']]);
        fputcsv($output, []);

        // Votos válidos e inválidos
        fputcsv($output, ['Votos Válidos e Inválidos']);
        fputcsv($output, ['Votos Válidos', $estadisticas['votos_validos_invalidos']['votos_validos'], $estadisticas['votos_validos_invalidos']['porcentaje_validos'] . '%']);
        fputcsv($output, ['Votos Inválidos', $estadisticas['votos_validos_invalidos']['votos_invalidos'], $estadisticas['votos_validos_invalidos']['porcentaje_invalidos'] . '%']);
        fputcsv($output, ['  - Votos Blancos', $estadisticas['votos_validos_invalidos']['votos_blancos']]);
        fputcsv($output, ['  - Votos Nulos', $estadisticas['votos_validos_invalidos']['votos_nulos']]);
        fputcsv($output, ['  - Votos Recurridos', $estadisticas['votos_validos_invalidos']['votos_recurridos']]);
        fputcsv($output, []);

        // Resultados Diputados
        fputcsv($output, ['RESULTADOS DIPUTADOS']);
        fputcsv($output, ['Lista', 'Total Votos', 'Total Telegramas']);
        foreach ($estadisticas['resultados_diputados'] as $lista) {
            fputcsv($output, [
                $lista['lista_nombre'],
                $lista['total_votos'],
                $lista['total_telegramas']
            ]);
        }
        fputcsv($output, []);

        // Resultados Senadores
        fputcsv($output, ['RESULTADOS SENADORES']);
        fputcsv($output, ['Lista', 'Total Votos', 'Total Telegramas']);
        foreach ($estadisticas['resultados_senadores'] as $lista) {
            fputcsv($output, [
                $lista['lista_nombre'],
                $lista['total_votos'],
                $lista['total_telegramas']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Caso de uso: Exportar resultados provinciales a JSON
     */
    public function exportarResultadosProvinciaJSON(int $provinciaId): string
    {
        $estadisticas = $this->obtenerEstadisticasProvincia($provinciaId);
        return json_encode($estadisticas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Caso de uso: Exportar resultados nacionales a CSV
     */
    public function exportarResultadosNacionalesCSV(): string
    {
        $resultados = $this->obtenerResultadosNacionales();

        // Crear archivo CSV en memoria
        $output = fopen('php://temp', 'r+');

        // Encabezados generales
        fputcsv($output, ['RESULTADOS ELECTORALES NACIONALES - ARGENTINA 2025']);
        fputcsv($output, []);

        // Participación
        fputcsv($output, ['Participación Electoral']);
        fputcsv($output, ['Total Electores', $resultados['participacion']['total_electores']]);
        fputcsv($output, ['Total Votos Emitidos', $resultados['participacion']['total_votos_emitidos']]);
        fputcsv($output, ['Participación (%)', $resultados['participacion']['porcentaje_participacion']]);
        fputcsv($output, ['Total Mesas', $resultados['participacion']['total_mesas']]);
        fputcsv($output, []);

        // Resultados Diputados
        fputcsv($output, ['RESULTADOS DIPUTADOS NACIONALES']);
        fputcsv($output, ['Total Votos:', $resultados['diputados']['total_votos']]);
        fputcsv($output, []);
        fputcsv($output, ['Lista', 'Total Votos', 'Porcentaje (%)', 'Total Mesas']);
        foreach ($resultados['diputados']['listas'] as $lista) {
            fputcsv($output, [
                $lista['lista_nombre'],
                $lista['total_votos'],
                $lista['porcentaje'],
                $lista['total_mesas']
            ]);
        }
        fputcsv($output, []);

        // Resultados Senadores
        fputcsv($output, ['RESULTADOS SENADORES NACIONALES']);
        fputcsv($output, ['Total Votos:', $resultados['senadores']['total_votos']]);
        fputcsv($output, []);
        fputcsv($output, ['Lista', 'Total Votos', 'Porcentaje (%)', 'Total Mesas']);
        foreach ($resultados['senadores']['listas'] as $lista) {
            fputcsv($output, [
                $lista['lista_nombre'],
                $lista['total_votos'],
                $lista['porcentaje'],
                $lista['total_mesas']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Caso de uso: Exportar resultados nacionales a JSON
     */
    public function exportarResultadosNacionalesJSON(): string
    {
        $resultados = $this->obtenerResultadosNacionales();
        return json_encode($resultados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Caso de uso: Exportar resumen de provincias a CSV
     */
    public function exportarResumenProvinciasCSV(): string
    {
        $resumen = $this->obtenerResumenProvincias();

        // Crear archivo CSV en memoria
        $output = fopen('php://temp', 'r+');

        // Encabezados
        fputcsv($output, ['RESUMEN ELECTORAL POR PROVINCIA - ARGENTINA 2025']);
        fputcsv($output, []);
        fputcsv($output, [
            'Provincia',
            'Bancas Diputados',
            'Bancas Senadores',
            'Total Mesas',
            'Total Electores',
            'Total Telegramas',
            'Total Votos',
            'Participación (%)'
        ]);

        // Datos
        foreach ($resumen as $provincia) {
            fputcsv($output, [
                $provincia['provincia_nombre'],
                $provincia['bancas_diputados'],
                $provincia['bancas_senadores'],
                $provincia['total_mesas'],
                $provincia['total_electores'],
                $provincia['total_telegramas'],
                $provincia['total_votos'],
                $provincia['porcentaje_participacion']
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Caso de uso: Exportar resumen de provincias a JSON
     */
    public function exportarResumenProvinciasJSON(): string
    {
        $resumen = $this->obtenerResumenProvincias();
        return json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
