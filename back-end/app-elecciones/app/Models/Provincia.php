<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\ProvinciaRepository;

/**
 * Provincia - Modelo de dominio con lógica de negocio
 * 
 * Responsabilidad:
 * - Representar el concepto de provincia dentro del dominio de la aplicación
 * - Contener la lógica de negocio (validaciones, reglas de negocio)
 * - NO saber cómo se persiste el dato (eso lo resuelve el Repository/DAO)
 */
class Provincia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'bancas_diputados',
        'bancas_senadores',
    ];

    // ==================== RELACIONES ====================

    public function listas()
    {
        return $this->hasMany(Lista::class);
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }

    // ==================== MÉTODOS DE CREACIÓN ====================

    /**
     * Crear una provincia desde datos de request
     * Este método actúa como un "factory method" del dominio
     */
    public static function crearDesdeRequest(string $nombre, ?int $bancasDiputados = null, ?int $bancasSenadores = 3): self
    {
        $provincia = new self();
        $provincia->nombre = trim($nombre);
        $provincia->bancas_diputados = $bancasDiputados;
        $provincia->bancas_senadores = $bancasSenadores;

        return $provincia;
    }

    // ==================== LÓGICA DE NEGOCIO ====================

    /**
     * Actualizar datos de la provincia
     */
    public function actualizarDatos(string $nombre, ?int $bancasDiputados = null, ?int $bancasSenadores = 3): void
    {
        $this->nombre = trim($nombre);
        $this->bancas_diputados = $bancasDiputados;
        $this->bancas_senadores = $bancasSenadores;
    }

    /**
     * Verificar que la provincia sea válida según reglas de negocio
     * 
     * Reglas:
     * - El nombre no puede estar vacío
     * - El nombre debe ser único en el sistema
     * - El número de bancas debe ser positivo (si se proporciona)
     */
    public function verificarQueSeaValida(ProvinciaRepository $repository, ?int $excludeId = null): void
    {
        // Validar que el nombre no esté vacío
        if (empty($this->nombre)) {
            throw new \InvalidArgumentException("El nombre de la provincia no puede estar vacío");
        }

        // Validar que el nombre sea único
        if ($repository->existeNombre($this->nombre, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe una provincia con el nombre: {$this->nombre}");
        }

        // Validar bancas si se proporciona
        if ($this->bancas_diputados !== null && $this->bancas_diputados < 1) {
            throw new \InvalidArgumentException("El número de bancas de diputados debe ser mayor a 0");
        }
        if ($this->bancas_senadores !== null && $this->bancas_senadores < 1) {
            throw new \InvalidArgumentException("El número de bancas de senadores debe ser mayor a 0");
        }
    }

    /**
     * Verificar que la provincia se pueda eliminar
     * 
     * Reglas:
     * - No se puede eliminar si tiene listas asociadas
     * - No se puede eliminar si tiene mesas asociadas
     */
    public function verificarQueSeaPuedeEliminar(): void
    {
        // Verificar si tiene listas
        if ($this->listas()->exists()) {
            throw new \Exception("No se puede eliminar la provincia porque tiene listas asociadas");
        }

        // Verificar si tiene mesas
        if ($this->mesas()->exists()) {
            throw new \Exception("No se puede eliminar la provincia porque tiene mesas asociadas");
        }
    }

    /**
     * Verificar si la provincia tiene datos relacionados
     */
    public function tieneDatosRelacionados(): bool
    {
        return $this->listas()->exists() || $this->mesas()->exists();
    }

    /**
     * Obtener descripción completa de la provincia
     */
    public function obtenerDescripcionCompleta(): string
    {
        $descripcion = $this->nombre;
        
        if ($this->bancas_diputados || $this->bancas_senadores) {
            $descripcion .= " (Diputados: {$this->bancas_diputados}, Senadores: {$this->bancas_senadores})";
        }

        return $descripcion;
    }

    // ==================== LÓGICA DE NEGOCIO - MÉTODO D'HONT ====================

    /**
     * Verificar si la provincia puede distribuir bancas para un cargo
     * 
     * @param string $cargo DIPUTADOS o SENADORES
     * @return bool
     */
    public function puedeDistribuirBancas(string $cargo): bool
    {
        if ($cargo === 'SENADORES') {
            return $this->bancas_senadores !== null && $this->bancas_senadores > 0;
        }
        return $this->bancas_diputados !== null && $this->bancas_diputados > 0;
    }

    /**
     * Obtener listas que compiten en esta provincia para un cargo
     * 
     * @param string $cargo DIPUTADOS o SENADORES
     * @return Collection Listas que pueden competir (con votos > 0)
     */
    public function obtenerListasCompetidoras(string $cargo): \Illuminate\Database\Eloquent\Collection
    {
        return $this->listas()
            ->where('cargo', $cargo)
            ->with('telegramas')
            ->get()
            ->filter(function($lista) {
                return $lista->tieneVotosParaComputar();
            });
    }

    /**
     * Distribuir bancas usando método D'Hont para DIPUTADOS
     * o sistema 2-1 para SENADORES
     * 
     * LÓGICA DE NEGOCIO PRINCIPAL:
     * - DIPUTADOS: Aplica el algoritmo D'Hont para distribución proporcional
     * - SENADORES: Sistema fijo de 3 bancas (2 al primero, 1 al segundo)
     * - Asigna bancas iterativamente al cociente más alto (solo para diputados)
     * - Retorna la distribución final por lista
     * 
     * @param string $cargo DIPUTADOS o SENADORES
     * @return array Distribución de bancas con detalles
     * @throws \Exception Si la provincia no puede distribuir bancas
     */
    public function distribuirBancasDHont(string $cargo): array
    {
        // Validar que se pueda distribuir
        if (!$this->puedeDistribuirBancas($cargo)) {
            throw new \Exception("La provincia '{$this->nombre}' no tiene bancas definidas para {$cargo}");
        }

        // Obtener número de bancas según el cargo
        $bancasTotales = $cargo === 'SENADORES' ? $this->bancas_senadores : $this->bancas_diputados;

        // Obtener listas competidoras
        $listas = $this->obtenerListasCompetidoras($cargo);

        if ($listas->isEmpty()) {
            return [
                'mensaje' => 'No hay listas con votos para este cargo',
                'listas' => []
            ];
        }

        // Inicializar bancas asignadas
        $bancasAsignadas = [];
        foreach ($listas as $lista) {
            $bancasAsignadas[$lista->id] = 0;
        }

        // DISTINCIÓN POR CARGO
        if ($cargo === 'SENADORES') {
            // SISTEMA SENADORES: 2 bancas al primero, 1 al segundo (total 3)
            $this->distribuirBancasSenadores($listas, $bancasAsignadas);
        } else {
            // ALGORITMO D'HONT PARA DIPUTADOS: Asignar bancas una por una
            for ($i = 0; $i < $bancasTotales; $i++) {
                $maxCociente = 0;
                $listaGanadora = null;

                // Calcular cocientes de todas las listas
                foreach ($listas as $lista) {
                    $cociente = $lista->calcularCocienteDHont($bancasAsignadas[$lista->id]);

                    if ($cociente > $maxCociente) {
                        $maxCociente = $cociente;
                        $listaGanadora = $lista;
                    }
                }

                // Asignar banca a la lista con mayor cociente
                if ($listaGanadora) {
                    $bancasAsignadas[$listaGanadora->id]++;
                }
            }
        }

        // Construir resultado
        return $this->construirResultadoDistribucion($listas, $bancasAsignadas, $bancasTotales);
    }

    /**
     * Distribuir bancas para SENADORES (sistema 2-1)
     * 
     * REGLA DE NEGOCIO:
     * - La lista más votada obtiene 2 bancas
     * - La segunda lista más votada obtiene 1 banca
     * - Total: 3 senadores por provincia
     * 
     * @param Collection $listas Listas que compiten
     * @param array &$bancasAsignadas Array de bancas por referencia
     */
    private function distribuirBancasSenadores($listas, array &$bancasAsignadas): void
    {
        // Ordenar listas por votos (mayor a menor)
        $listasOrdenadas = $listas->sortByDesc(function($lista) {
            return $lista->calcularVotosTotales();
        })->values();

        // Primera lista: 2 bancas
        if (isset($listasOrdenadas[0])) {
            $bancasAsignadas[$listasOrdenadas[0]->id] = 2;
        }

        // Segunda lista: 1 banca
        if (isset($listasOrdenadas[1])) {
            $bancasAsignadas[$listasOrdenadas[1]->id] = 1;
        }
    }

    /**
     * Construir el resultado de la distribución
     * 
     * @param Collection $listas Listas que participaron
     * @param array $bancasAsignadas Bancas asignadas por lista
     * @param int $bancasTotales Total de bancas a distribuir
     * @return array Resultado con votos y bancas
     */
    private function construirResultadoDistribucion($listas, array $bancasAsignadas, int $bancasTotales): array
    {
        $resultado = [];

        foreach ($listas as $lista) {
            $votos = $lista->calcularVotosTotales();
            $bancas = $bancasAsignadas[$lista->id];

            // Obtener candidatos electos
            $candidatosElectos = $lista->obtenerCandidatosElectos($bancas);
            
            $resultado[] = [
                'lista_id' => $lista->id,
                'nombre' => $lista->nombre,
                'alianza' => $lista->alianza,
                'votos' => $votos,
                'bancas_asignadas' => $bancas,
                'candidatos_electos' => $candidatosElectos->map(function($candidato) {
                    return [
                        'candidato_id' => $candidato->id,
                        'nombre' => $candidato->nombre,
                        'orden' => $candidato->orden_en_lista,
                    ];
                })->toArray(),
                'tiene_suficientes_candidatos' => $lista->tieneSuficientesCandidatos($bancas),
            ];
        }

        // Ordenar por bancas asignadas (mayor a menor)
        usort($resultado, function($a, $b) {
            if ($b['bancas_asignadas'] === $a['bancas_asignadas']) {
                return $b['votos'] <=> $a['votos'];
            }
            return $b['bancas_asignadas'] <=> $a['bancas_asignadas'];
        });

        $votosTotales = $listas->sum(fn($lista) => $lista->calcularVotosTotales());

        return [
            'votos_totales' => $votosTotales,
            'listas' => $resultado
        ];
    }
}
