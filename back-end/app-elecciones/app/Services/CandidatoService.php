<?php

namespace App\Services;

use App\Repositories\CandidatoRepository;
use App\Models\Candidato;
use Illuminate\Database\Eloquent\Collection;

//Servicio de orquestación de casos de uso de candidatos
class CandidatoService
{
    private CandidatoRepository $candidatoRepository;

    public function __construct(CandidatoRepository $candidatoRepository)
    {
        $this->candidatoRepository = $candidatoRepository;
    }

    //Listar todos los candidatos
    public function listarCandidatos(): Collection
    {
        return $this->candidatoRepository->obtenerTodos();
    }

    //Obtener candidato por ID
    public function obtenerCandidato(int $id): ?Candidato
    {
        return $this->candidatoRepository->buscarPorId($id);
    }

    //Registrar nuevo candidato
    public function registrarCandidato(array $datos): Candidato
    {
        $candidato = Candidato::crearDesdeRequest(
            $datos['nombre'],
            $datos['orden_en_lista'],
            $datos['lista_id']
        );

        $candidato->verificarQueSeaValido($this->candidatoRepository);
        $this->candidatoRepository->guardar($candidato);
        return $this->candidatoRepository->buscarPorId($candidato->id);
    }

    //Actualizar candidato existente
    public function actualizarCandidato(int $id, array $datos): Candidato
    {
        $candidato = $this->candidatoRepository->buscarPorId($id);

        if (!$candidato) {
            throw new \Exception("Candidato no encontrado con ID: {$id}");
        }

        $candidato->actualizarDatos(
            $datos['nombre'],
            $datos['orden_en_lista'],
            $datos['lista_id']
        );

        $candidato->verificarQueSeaValido($this->candidatoRepository, $id);
        $this->candidatoRepository->actualizar($candidato);
        return $this->candidatoRepository->buscarPorId($id);
    }

    //Eliminar candidato
    public function eliminarCandidato(int $id): array
    {
        $candidato = $this->candidatoRepository->buscarPorId($id);

        if (!$candidato) {
            throw new \Exception("Candidato no encontrado con ID: {$id}");
        }

        $this->candidatoRepository->eliminar($id);

        return ['mensaje' => 'Candidato eliminado correctamente'];
    }
}
