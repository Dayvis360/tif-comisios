<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Repositories\CandidatoRepository;

//Modelo de dominio de candidato con lógica de negocio
class Candidato extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'orden_en_lista',
        'lista_id',
    ];

    public function lista()
    {
        return $this->belongsTo(Lista::class);
    }

    //Crear candidato desde datos de request
    public static function crearDesdeRequest(string $nombre, int $ordenEnLista, int $listaId): self
    {
        $candidato = new self();
        $candidato->nombre = trim($nombre);
        $candidato->orden_en_lista = $ordenEnLista;
        $candidato->lista_id = $listaId;

        return $candidato;
    }

    //Actualizar datos del candidato
    public function actualizarDatos(string $nombre, int $ordenEnLista, int $listaId): void
    {
        $this->nombre = trim($nombre);
        $this->orden_en_lista = $ordenEnLista;
        $this->lista_id = $listaId;
    }

    //Verificar que el candidato sea válido
    public function verificarQueSeaValido(CandidatoRepository $repository, ?int $excludeId = null): void
    {
        if (empty($this->nombre)) {
            throw new \InvalidArgumentException("El nombre del candidato no puede estar vacío");
        }

        if ($this->orden_en_lista < 1) {
            throw new \InvalidArgumentException("El orden en lista debe ser mayor a 0");
        }

        if ($repository->existeOrdenEnLista($this->lista_id, $this->orden_en_lista, $excludeId)) {
            throw new \InvalidArgumentException("Ya existe un candidato en la posición {$this->orden_en_lista} de esta lista");
        }
    }

    //Obtener descripción completa del candidato
    public function obtenerDescripcionCompleta(): string
    {
        return "{$this->orden_en_lista}. {$this->nombre}";
    }
}
