<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\ProvinciaRepository;

//Modelo de dominio de provincia con lógica de negocio
class Provincia extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'bancas_diputados',
        'bancas_senadores',
    ];

    public function listas()
    {
        return $this->hasMany(Lista::class);
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class);
    }

    //Crear provincia desde datos de request
    public static function crearDesdeRequest(string $nombre, ?int $bancasDiputados = null, ?int $bancasSenadores = 3): self
    {
        $provincia = new self();
        $provincia->nombre = trim($nombre);
        $provincia->bancas_diputados = $bancasDiputados;
        $provincia->bancas_senadores = $bancasSenadores;

        return $provincia;
    }

    //Actualizar datos de la provincia
    public function actualizarDatos(string $nombre, ?int $bancasDiputados = null, ?int $bancasSenadores = 3): void
    {
        $this->nombre = trim($nombre);
        $this->bancas_diputados = $bancasDiputados;
        $this->bancas_senadores = $bancasSenadores;
    }

    //Verificar que la provincia sea válida según reglas de negocio
    public function verificarQueSeaValida(ProvinciaRepository $repository, ?int $excludeId = null): void
    {
        if (empty($this->nombre)) {
            throw new \InvalidArgumentException("El nombre de la provincia no puede estar vacío");
        }

        if ($repository->existeNombre($this->nombre, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe una provincia con el nombre: {$this->nombre}");
        }

        if ($this->bancas_diputados !== null && $this->bancas_diputados < 1) {
            throw new \InvalidArgumentException("El número de bancas de diputados debe ser mayor a 0");
        }
        if ($this->bancas_senadores !== null && $this->bancas_senadores < 1) {
            throw new \InvalidArgumentException("El número de bancas de senadores debe ser mayor a 0");
        }
    }

    //Verificar que la provincia se pueda eliminar
    public function verificarQueSeaPuedeEliminar(): void
    {
        if ($this->listas()->exists()) {
            throw new \Exception("No se puede eliminar la provincia porque tiene listas asociadas");
        }

        if ($this->mesas()->exists()) {
            throw new \Exception("No se puede eliminar la provincia porque tiene mesas asociadas");
        }
    }

    //Verificar si la provincia tiene datos relacionados
    public function tieneDatosRelacionados(): bool
    {
        return $this->listas()->exists() || $this->mesas()->exists();
    }

    //Obtener descripción completa de la provincia
    public function obtenerDescripcionCompleta(): string
    {
        $descripcion = $this->nombre;
        
        if ($this->bancas_diputados || $this->bancas_senadores) {
            $descripcion .= " (Diputados: {$this->bancas_diputados}, Senadores: {$this->bancas_senadores})";
        }

        return $descripcion;
    }

    //Verificar si la provincia puede distribuir bancas para un cargo
    public function puedeDistribuirBancas(string $cargo): bool
    {
        if ($cargo === 'SENADORES') {
            return $this->bancas_senadores !== null && $this->bancas_senadores > 0;
        }
        return $this->bancas_diputados !== null && $this->bancas_diputados > 0;
    }

    //Obtener listas que compiten en esta provincia para un cargo
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

    //Distribuir bancas usando método D'Hont para DIPUTADOS o sistema 2-1 para SENADORES
    public function distribuirBancasDHont(string $cargo): array
    {
        if (!$this->puedeDistribuirBancas($cargo)) {
            throw new \Exception("La provincia '{$this->nombre}' no tiene bancas definidas para {$cargo}");
        }

        $bancasTotales = $cargo === 'SENADORES' ? $this->bancas_senadores : $this->bancas_diputados;
        $listas = $this->obtenerListasCompetidoras($cargo);

        if ($listas->isEmpty()) {
            return [
                'mensaje' => 'No hay listas con votos para este cargo',
                'listas' => []
            ];
        }

        $bancasAsignadas = [];
        foreach ($listas as $lista) {
            $bancasAsignadas[$lista->id] = 0;
        }

        if ($cargo === 'SENADORES') {
            $this->distribuirBancasSenadores($listas, $bancasAsignadas);
        } else {
            for ($i = 0; $i < $bancasTotales; $i++) {
                $maxCociente = 0;
                $listaGanadora = null;

                foreach ($listas as $lista) {
                    $cociente = $lista->calcularCocienteDHont($bancasAsignadas[$lista->id]);

                    if ($cociente > $maxCociente) {
                        $maxCociente = $cociente;
                        $listaGanadora = $lista;
                    }
                }

                if ($listaGanadora) {
                    $bancasAsignadas[$listaGanadora->id]++;
                }
            }
        }

        return $this->construirResultadoDistribucion($listas, $bancasAsignadas, $bancasTotales);
    }

    //Distribuir bancas para SENADORES (sistema 2-1)
    private function distribuirBancasSenadores($listas, array &$bancasAsignadas): void
    {
        $listasOrdenadas = $listas->sortByDesc(function($lista) {
            return $lista->calcularVotosTotales();
        })->values();

        if (isset($listasOrdenadas[0])) {
            $bancasAsignadas[$listasOrdenadas[0]->id] = 2;
        }

        if (isset($listasOrdenadas[1])) {
            $bancasAsignadas[$listasOrdenadas[1]->id] = 1;
        }
    }

    //Construir el resultado de la distribución
    private function construirResultadoDistribucion($listas, array $bancasAsignadas, int $bancasTotales): array
    {
        $resultado = [];

        foreach ($listas as $lista) {
            $votos = $lista->calcularVotosTotales();
            $bancas = $bancasAsignadas[$lista->id];

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
