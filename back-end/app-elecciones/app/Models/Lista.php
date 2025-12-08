<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\ListaRepository;

/**
 * Lista - Modelo de dominio con lógica de negocio
 * 
 * Responsabilidad:
 * - Representar el concepto de lista electoral
 * - Contener la lógica de negocio
 */
class Lista extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'alianza',
        'cargo',
        'provincia_id',
    ];

    // ==================== RELACIONES ====================

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function candidatos()
    {
        return $this->hasMany(Candidato::class);
    }

    public function telegramas()
    {
        return $this->hasMany(Telegrama::class);
    }

    // ==================== MÉTODOS DE CREACIÓN ====================

    /**
     * Crear una lista desde datos de request
     */
    public static function crearDesdeRequest(string $nombre, string $cargo, int $provinciaId, ?string $alianza = null): self
    {
        $lista = new self();
        $lista->nombre = trim($nombre);
        $lista->cargo = strtoupper(trim($cargo));
        $lista->provincia_id = $provinciaId;
        $lista->alianza = $alianza ? trim($alianza) : null;

        return $lista;
    }

    // ==================== LÓGICA DE NEGOCIO ====================

    /**
     * Actualizar datos de la lista
     */
    public function actualizarDatos(string $nombre, string $cargo, int $provinciaId, ?string $alianza = null): void
    {
        $this->nombre = trim($nombre);
        $this->cargo = strtoupper(trim($cargo));
        $this->provincia_id = $provinciaId;
        $this->alianza = $alianza ? trim($alianza) : null;
    }

    /**
     * Verificar que la lista sea válida
     * 
     * Reglas:
     * - El nombre no puede estar vacío
     * - El cargo debe ser válido (DIPUTADOS o SENADORES)
     * - No puede existir otra lista con mismo nombre, provincia y cargo
     */
    public function verificarQueSeaValida(ListaRepository $repository, ?int $excludeId = null): void
    {
        if (empty($this->nombre)) {
            throw new \InvalidArgumentException("El nombre de la lista no puede estar vacío");
        }

        // Validar cargo
        $cargosValidos = ['DIPUTADOS', 'SENADORES'];
        if (!in_array($this->cargo, $cargosValidos)) {
            throw new \InvalidArgumentException("El cargo debe ser DIPUTADOS o SENADORES");
        }

        // Verificar unicidad
        if ($repository->existeListaEnProvincia($this->nombre, $this->provincia_id, $this->cargo, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe una lista '{$this->nombre}' para {$this->cargo} en esta provincia");
        }
    }

    /**
     * Verificar que la lista se pueda eliminar
     * 
     * Reglas:
     * - No se puede eliminar si tiene candidatos
     * - No se puede eliminar si tiene telegramas
     */
    public function verificarQueSeaPuedeEliminar(): void
    {
        if ($this->candidatos()->exists()) {
            throw new \Exception("No se puede eliminar la lista porque tiene candidatos asociados");
        }

        if ($this->telegramas()->exists()) {
            throw new \Exception("No se puede eliminar la lista porque tiene telegramas asociados");
        }
    }

    /**
     * Obtener descripción completa
     */
    public function obtenerDescripcionCompleta(): string
    {
        $descripcion = $this->nombre;
        
        if ($this->alianza) {
            $descripcion .= " ({$this->alianza})";
        }

        $descripcion .= " - {$this->cargo}";

        return $descripcion;
    }

    // ==================== LÓGICA DE NEGOCIO - MÉTODO D'HONT ====================

    /**
     * Calcular votos totales de esta lista para un cargo específico
     * 
     * Regla de negocio: Los votos se suman de todos los telegramas asociados
     * según el cargo (DIPUTADOS o SENADORES)
     */
    public function calcularVotosTotales(): int
    {
        $votosTotal = 0;

        foreach ($this->telegramas as $telegrama) {
            if ($this->cargo === 'DIPUTADOS') {
                $votosTotal += $telegrama->votos_Diputados;
            } else if ($this->cargo === 'SENADORES') {
                $votosTotal += $telegrama->votos_Senadores;
            }
        }

        return $votosTotal;
    }

    /**
     * Verificar si la lista tiene suficientes votos para competir
     * 
     * Regla de negocio: Una lista debe tener al menos 1 voto para participar
     * en la distribución de bancas
     */
    public function tieneVotosParaComputar(): bool
    {
        return $this->calcularVotosTotales() > 0;
    }

    /**
     * Calcular el cociente D'Hont para esta lista
     * 
     * Fórmula D'Hont: Votos / (Bancas_asignadas + 1)
     * 
     * @param int $bancasYaAsignadas Número de bancas que ya tiene esta lista
     * @return float Cociente D'Hont
     */
    public function calcularCocienteDHont(int $bancasYaAsignadas): float
    {
        $votos = $this->calcularVotosTotales();
        $divisor = $bancasYaAsignadas + 1;
        
        return $votos / $divisor;
    }

    /**
     * Obtener candidatos electos según las bancas ganadas
     * 
     * LÓGICA DE NEGOCIO:
     * - Los candidatos se eligen según su orden en la lista
     * - Si la lista ganó 3 bancas, se eligen los primeros 3 candidatos
     * - Ordenados por orden_en_lista ASC
     * 
     * @param int $bancasGanadas Número de bancas que ganó esta lista
     * @return Collection Candidatos electos
     */
    public function obtenerCandidatosElectos(int $bancasGanadas): \Illuminate\Database\Eloquent\Collection
    {
        if ($bancasGanadas <= 0) {
            return new \Illuminate\Database\Eloquent\Collection([]);
        }

        return $this->candidatos()
            ->orderBy('orden_en_lista', 'asc')
            ->limit($bancasGanadas)
            ->get();
    }

    /**
     * Verificar si la lista tiene suficientes candidatos para las bancas ganadas
     * 
     * REGLA DE NEGOCIO: Una lista debería tener al menos tantos candidatos
     * como bancas puede ganar
     * 
     * @param int $bancasGanadas
     * @return bool
     */
    public function tieneSuficientesCandidatos(int $bancasGanadas): bool
    {
        return $this->candidatos()->count() >= $bancasGanadas;
    }
}
