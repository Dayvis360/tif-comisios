<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\TelegramaRepository;

/**
 * Telegrama - Modelo de dominio con lógica de negocio
 * 
 * Responsabilidad:
 * - Representar el concepto de telegrama electoral
 * - Contener la lógica de negocio (validaciones de votos)
 */
class Telegrama extends Model
{
    use HasFactory;

    protected $fillable = [
        'mesa_id',
        'lista_id',
        'votos_Diputados',
        'votos_Senadores',
        'voto_Blancos',
        'voto_Nulos',
        'voto_Recurridos',
        'usuario_carga',
        'fecha_carga',
        'usuario_modificacion',
        'fecha_modificacion'
    ];

    // ==================== RELACIONES ====================

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function lista()
    {
        return $this->belongsTo(Lista::class);
    }

    // ==================== MÉTODOS DE CREACIÓN ====================

    /**
     * Crear un telegrama desde datos de request
     */
    public static function crearDesdeRequest(
        int $mesaId,
        int $listaId,
        int $votosDiputados,
        int $votosSenadores,
        int $votoBlancos,
        int $votoNulos,
        int $votoRecurridos,
        ?string $usuario = null
    ): self {
        $telegrama = new self();
        $telegrama->mesa_id = $mesaId;
        $telegrama->lista_id = $listaId;
        $telegrama->votos_Diputados = $votosDiputados;
        $telegrama->votos_Senadores = $votosSenadores;
        $telegrama->voto_Blancos = $votoBlancos;
        $telegrama->voto_Nulos = $votoNulos;
        $telegrama->voto_Recurridos = $votoRecurridos;
        $telegrama->usuario_carga = $usuario ?? 'Sistema';
        $telegrama->fecha_carga = now();

        return $telegrama;
    }

    // ==================== LÓGICA DE NEGOCIO ====================

    /**
     * Actualizar datos del telegrama
     */
    public function actualizarDatos(
        int $mesaId,
        int $listaId,
        int $votosDiputados,
        int $votosSenadores,
        int $votoBlancos,
        int $votoNulos,
        int $votoRecurridos,
        ?string $usuario = null
    ): void {
        $this->mesa_id = $mesaId;
        $this->lista_id = $listaId;
        $this->votos_Diputados = $votosDiputados;
        $this->votos_Senadores = $votosSenadores;
        $this->voto_Blancos = $votoBlancos;
        $this->voto_Nulos = $votoNulos;
        $this->voto_Recurridos = $votoRecurridos;
        $this->usuario_modificacion = $usuario ?? 'Sistema';
        $this->fecha_modificacion = now();
    }

    /**
     * Verificar que el telegrama sea válido
     * 
     * Reglas:
     * - Todos los votos deben ser mayores o iguales a 0
     * - No puede existir otro telegrama para la misma mesa y lista
     * - El total de votos no puede exceder el número de electores de la mesa (validación opcional)
     */
    public function verificarQueSeaValido(TelegramaRepository $repository, ?int $excludeId = null): void
    {
        // Validar que los votos no sean negativos
        if ($this->votos_Diputados < 0) {
            throw new \InvalidArgumentException("Los votos para diputados no pueden ser negativos");
        }

        if ($this->votos_Senadores < 0) {
            throw new \InvalidArgumentException("Los votos para senadores no pueden ser negativos");
        }

        if ($this->voto_Blancos < 0) {
            throw new \InvalidArgumentException("Los votos en blanco no pueden ser negativos");
        }

        if ($this->voto_Nulos < 0) {
            throw new \InvalidArgumentException("Los votos nulos no pueden ser negativos");
        }

        if ($this->voto_Recurridos < 0) {
            throw new \InvalidArgumentException("Los votos recurridos no pueden ser negativos");
        }

        // Verificar que no exista telegrama duplicado para mesa y lista
        if ($repository->existeTelegramaParaMesaYLista($this->mesa_id, $this->lista_id, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe un telegrama para esta mesa y lista");
        }
    }

    /**
     * Calcular total de votos del telegrama
     */
    public function calcularTotalVotos(): int
    {
        return $this->votos_Diputados + 
               $this->votos_Senadores + 
               $this->voto_Blancos + 
               $this->voto_Nulos + 
               $this->voto_Recurridos;
    }

    /**
     * Verificar si el telegrama tiene votos válidos (no solo blancos/nulos/recurridos)
     */
    public function tieneVotosValidos(): bool
    {
        return $this->votos_Diputados > 0 || $this->votos_Senadores > 0;
    }

    /**
     * Validar estructura de datos para importación
     * Verifica que tenga todos los campos obligatorios
     */
    public static function validarEstructuraImportacion(array $dato): array
    {
        $errores = [];

        // Campos obligatorios
        if (!isset($dato['mesa_id'])) {
            $errores[] = 'Falta el campo mesa_id';
        }

        if (!isset($dato['lista_id'])) {
            $errores[] = 'Falta el campo lista_id';
        }

        if (!isset($dato['votos_Diputados'])) {
            $errores[] = 'Falta el campo votos_Diputados';
        }

        if (!isset($dato['votos_Senadores'])) {
            $errores[] = 'Falta el campo votos_Senadores';
        }

        if (!isset($dato['voto_Blancos'])) {
            $errores[] = 'Falta el campo voto_Blancos';
        }

        if (!isset($dato['voto_Nulos'])) {
            $errores[] = 'Falta el campo voto_Nulos';
        }

        if (!isset($dato['voto_Recurridos'])) {
            $errores[] = 'Falta el campo voto_Recurridos';
        }

        // Validar que sean números
        if (isset($dato['mesa_id']) && !is_numeric($dato['mesa_id'])) {
            $errores[] = 'El campo mesa_id debe ser numérico';
        }

        if (isset($dato['lista_id']) && !is_numeric($dato['lista_id'])) {
            $errores[] = 'El campo lista_id debe ser numérico';
        }

        if (isset($dato['votos_Diputados']) && !is_numeric($dato['votos_Diputados'])) {
            $errores[] = 'El campo votos_Diputados debe ser numérico';
        }

        if (isset($dato['votos_Senadores']) && !is_numeric($dato['votos_Senadores'])) {
            $errores[] = 'El campo votos_Senadores debe ser numérico';
        }

        if (isset($dato['voto_Blancos']) && !is_numeric($dato['voto_Blancos'])) {
            $errores[] = 'El campo voto_Blancos debe ser numérico';
        }

        if (isset($dato['voto_Nulos']) && !is_numeric($dato['voto_Nulos'])) {
            $errores[] = 'El campo voto_Nulos debe ser numérico';
        }

        if (isset($dato['voto_Recurridos']) && !is_numeric($dato['voto_Recurridos'])) {
            $errores[] = 'El campo voto_Recurridos debe ser numérico';
        }

        // Validar que sean positivos
        if (isset($dato['votos_Diputados']) && $dato['votos_Diputados'] < 0) {
            $errores[] = 'Los votos_Diputados no pueden ser negativos';
        }

        if (isset($dato['votos_Senadores']) && $dato['votos_Senadores'] < 0) {
            $errores[] = 'Los votos_Senadores no pueden ser negativos';
        }

        if (isset($dato['voto_Blancos']) && $dato['voto_Blancos'] < 0) {
            $errores[] = 'Los voto_Blancos no pueden ser negativos';
        }

        if (isset($dato['voto_Nulos']) && $dato['voto_Nulos'] < 0) {
            $errores[] = 'Los voto_Nulos no pueden ser negativos';
        }

        if (isset($dato['voto_Recurridos']) && $dato['voto_Recurridos'] < 0) {
            $errores[] = 'Los voto_Recurridos no pueden ser negativos';
        }

        return $errores;
    }
}
