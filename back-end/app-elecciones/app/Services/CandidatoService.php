<?php

namespace App\Services;

use App\Repositories\CandidatoRepository;
use App\Models\Candidato;
use Illuminate\Database\Eloquent\Collection;

/**
 * CandidatoService
 * 
 * Responsabilidad:
 * - Orquestar los casos de uso relacionados con candidatos
 * - Coordinar llamadas hacia el Modelo y el Repository
 */
class CandidatoService
{
    private CandidatoRepository $candidatoRepository;

    public function __construct(CandidatoRepository $candidatoRepository)
    {
        $this->candidatoRepository = $candidatoRepository;
    }

    /**
     * Caso de uso: Listar todos los candidatos
     */
    public function listarCandidatos(): Collection
    {
        return $this->candidatoRepository->obtenerTodos();
    }

    /**
     * Caso de uso: Obtener un candidato por ID
     */
    public function obtenerCandidato(int $id): ?Candidato
    {
        return $this->candidatoRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Registrar un nuevo candidato
     */
    public function registrarCandidato(array $datos): Candidato
    {
        // 1. Crear el modelo de dominio
        $candidato = Candidato::crearDesdeRequest(
            $datos['nombre'],
            $datos['orden_en_lista'],
            $datos['lista_id']
        );

        // 2. Aplicar reglas de negocio
        $candidato->verificarQueSeaValido($this->candidatoRepository);

        // 3. Guardar
        $this->candidatoRepository->guardar($candidato);

        // 4. Retornar con relaciones
        return $this->candidatoRepository->buscarPorId($candidato->id);
    }

    /**
     * Caso de uso: Actualizar un candidato existente
     */
    public function actualizarCandidato(int $id, array $datos): Candidato
    {
        // 1. Buscar el candidato
        $candidato = $this->candidatoRepository->buscarPorId($id);

        if (!$candidato) {
            throw new \Exception("Candidato no encontrado con ID: {$id}");
        }

        // 2. Actualizar datos
        $candidato->actualizarDatos(
            $datos['nombre'],
            $datos['orden_en_lista'],
            $datos['lista_id']
        );

        // 3. Aplicar reglas de negocio
        $candidato->verificarQueSeaValido($this->candidatoRepository, $id);

        // 4. Guardar cambios
        $this->candidatoRepository->actualizar($candidato);

        // 5. Retornar actualizado
        return $this->candidatoRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Eliminar un candidato
     */
    public function eliminarCandidato(int $id): array
    {
        // 1. Buscar el candidato
        $candidato = $this->candidatoRepository->buscarPorId($id);

        if (!$candidato) {
            throw new \Exception("Candidato no encontrado con ID: {$id}");
        }

        // 2. Verificar que se pueda eliminar (en este caso no hay restricciones adicionales)
        // Si en el futuro hay reglas, se agregan aquí

        // 3. Eliminar
        $this->candidatoRepository->eliminar($id);

        return ['mensaje' => 'Candidato eliminado correctamente'];
    }
}
