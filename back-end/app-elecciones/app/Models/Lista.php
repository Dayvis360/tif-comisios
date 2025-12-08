<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\ListaRepository;

//Modelo de dominio de lista electoral con lógica de negocio
class Lista extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'alianza',
        'cargo',
        'provincia_id',
    ];

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

    //Crear lista desde datos de request
    public static function crearDesdeRequest(string $nombre, string $cargo, int $provinciaId, ?string $alianza = null): self
    {
        $lista = new self();
        $lista->nombre = trim($nombre);
        $lista->cargo = strtoupper(trim($cargo));
        $lista->provincia_id = $provinciaId;
        $lista->alianza = $alianza ? trim($alianza) : null;

        return $lista;
    }

    //Actualizar datos de la lista
    public function actualizarDatos(string $nombre, string $cargo, int $provinciaId, ?string $alianza = null): void
    {
        $this->nombre = trim($nombre);
        $this->cargo = strtoupper(trim($cargo));
        $this->provincia_id = $provinciaId;
        $this->alianza = $alianza ? trim($alianza) : null;
    }

    //Verificar que la lista sea válida
    public function verificarQueSeaValida(ListaRepository $repository, ?int $excludeId = null): void
    {
        if (empty($this->nombre)) {
            throw new \InvalidArgumentException("El nombre de la lista no puede estar vacío");
        }

        $cargosValidos = ['DIPUTADOS', 'SENADORES'];
        if (!in_array($this->cargo, $cargosValidos)) {
            throw new \InvalidArgumentException("El cargo debe ser DIPUTADOS o SENADORES");
        }

        if ($repository->existeListaEnProvincia($this->nombre, $this->provincia_id, $this->cargo, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe una lista '{$this->nombre}' para {$this->cargo} en esta provincia");
        }
    }

    //Verificar que la lista se pueda eliminar
    public function verificarQueSeaPuedeEliminar(): void
    {
        if ($this->candidatos()->exists()) {
            throw new \Exception("No se puede eliminar la lista porque tiene candidatos asociados");
        }

        if ($this->telegramas()->exists()) {
            throw new \Exception("No se puede eliminar la lista porque tiene telegramas asociados");
        }
    }

    //Obtener descripción completa
    public function obtenerDescripcionCompleta(): string
    {
        $descripcion = $this->nombre;
        
        if ($this->alianza) {
            $descripcion .= " ({$this->alianza})";
        }

        $descripcion .= " - {$this->cargo}";

        return $descripcion;
    }

    //Calcular votos totales de esta lista para un cargo específico
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

    //Verificar si la lista tiene suficientes votos para competir
    public function tieneVotosParaComputar(): bool
    {
        return $this->calcularVotosTotales() > 0;
    }

    //Calcular el cociente D'Hont para esta lista (Votos / (Bancas_asignadas + 1))
    public function calcularCocienteDHont(int $bancasYaAsignadas): float
    {
        $votos = $this->calcularVotosTotales();
        $divisor = $bancasYaAsignadas + 1;
        
        return $votos / $divisor;
    }

    //Obtener candidatos electos según las bancas ganadas
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

    //Verificar si la lista tiene suficientes candidatos para las bancas ganadas
    public function tieneSuficientesCandidatos(int $bancasGanadas): bool
    {
        return $this->candidatos()->count() >= $bancasGanadas;
    }
}
