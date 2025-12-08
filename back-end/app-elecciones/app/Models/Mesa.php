<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\MesaRepository;

/**
 * Mesa - Modelo de dominio con lógica de negocio
 * 
 * Responsabilidad:
 * - Representar el concepto de mesa electoral
 * - Contener la lógica de negocio (validaciones, reglas)
 * - NO saber cómo se persiste el dato
 */
class Mesa extends Model
{
    use HasFactory;

    protected $fillable = [
        'provincia_id',
        'circuito',
        'establecimiento',
        'electores'
    ];

    // ==================== RELACIONES ====================

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function telegramas()
    {
        return $this->hasMany(Telegrama::class);
    }

    // ==================== MÉTODOS DE CREACIÓN ====================

    /**
     * Crear una mesa desde datos de request
     */
    public static function crearDesdeRequest(int $provinciaId, string $circuito, string $establecimiento, int $electores): self
    {
        $mesa = new self();
        $mesa->provincia_id = $provinciaId;
        $mesa->circuito = trim($circuito);
        $mesa->establecimiento = trim($establecimiento);
        $mesa->electores = $electores;

        return $mesa;
    }

    // ==================== LÓGICA DE NEGOCIO ====================

    /**
     * Actualizar datos de la mesa
     */
    public function actualizarDatos(int $provinciaId, string $circuito, string $establecimiento, int $electores): void
    {
        $this->provincia_id = $provinciaId;
        $this->circuito = trim($circuito);
        $this->establecimiento = trim($establecimiento);
        $this->electores = $electores;
    }

    /**
     * Verificar que la mesa sea válida
     * 
     * Reglas:
     * - La provincia debe existir
     * - El circuito no puede estar vacío
     * - El establecimiento no puede estar vacío
     * - No puede existir otra mesa con mismo circuito y establecimiento en la provincia
     * - Los electores deben ser mayor a 0
     */
    public function verificarQueSeaValida(MesaRepository $repository, ?int $excludeId = null): void
    {
        if (empty($this->circuito)) {
            throw new \InvalidArgumentException("El circuito no puede estar vacío");
        }

        if (empty($this->establecimiento)) {
            throw new \InvalidArgumentException("El establecimiento no puede estar vacío");
        }

        if ($this->electores < 1) {
            throw new \InvalidArgumentException("El número de electores debe ser mayor a 0");
        }

        // Verificar que no exista mesa duplicada
        if ($repository->existeMesaEnCircuito($this->provincia_id, $this->circuito, $this->establecimiento, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe una mesa en el circuito {$this->circuito} del establecimiento {$this->establecimiento}");
        }
    }

    /**
     * Verificar que la mesa se pueda eliminar
     * 
     * Reglas:
     * - No se puede eliminar si tiene telegramas asociados
     */
    public function verificarQueSeaPuedeEliminar(): void
    {
        if ($this->telegramas()->exists()) {
            throw new \Exception("No se puede eliminar la mesa porque tiene telegramas asociados");
        }
    }

    /**
     * Obtener descripción completa de la mesa
     */
    public function obtenerDescripcionCompleta(): string
    {
        return "Mesa {$this->circuito} - {$this->establecimiento} ({$this->electores} electores)";
    }
}
