<?php

namespace App\Services;

use App\Repositories\ProvinciaRepository;
use App\Models\Provincia;
use Illuminate\Database\Eloquent\Collection;

//Servicio de orquestación de casos de uso de provincias
class ProvinciaService
{
    private ProvinciaRepository $provinciaRepository;

    public function __construct(ProvinciaRepository $provinciaRepository)
    {
        $this->provinciaRepository = $provinciaRepository;
    }

    //Listar todas las provincias
    public function listarProvincias(): Collection
    {
        return $this->provinciaRepository->obtenerTodas();
    }

    //Obtener provincia por ID
    public function obtenerProvincia(int $id): ?Provincia
    {
        return $this->provinciaRepository->buscarPorId($id);
    }

    //Registrar nueva provincia
    public function registrarProvincia(array $datos): Provincia
    {
        $provincia = Provincia::crearDesdeRequest(
            $datos['nombre'],
            $datos['bancas_diputados'] ?? null,
            $datos['bancas_senadores'] ?? 3
        );

        $provincia->verificarQueSeaValida($this->provinciaRepository);
        $this->provinciaRepository->guardar($provincia);
        return $provincia;
    }

    //Actualizar provincia existente
    public function actualizarProvincia(int $id, array $datos): Provincia
    {
        $provincia = $this->provinciaRepository->buscarPorId($id);

        if (!$provincia) {
            throw new \Exception("Provincia no encontrada con ID: {$id}");
        }

        $provincia->actualizarDatos(
            $datos['nombre'],
            $datos['bancas_diputados'] ?? $provincia->bancas_diputados,
            $datos['bancas_senadores'] ?? $provincia->bancas_senadores
        );

        $provincia->verificarQueSeaValida($this->provinciaRepository, $id);
        $this->provinciaRepository->actualizar($provincia);
        return $provincia;
    }

    //Eliminar provincia
    public function eliminarProvincia(int $id): array
    {
        $provincia = $this->provinciaRepository->buscarPorId($id);

        if (!$provincia) {
            throw new \Exception("Provincia no encontrada con ID: {$id}");
        }

        $provincia->verificarQueSeaPuedeEliminar();
        $this->provinciaRepository->eliminar($id);
        return ['mensaje' => 'Provincia eliminada correctamente'];
    }

    //Verificar si existe provincia por nombre
    public function existeProvinciaPorNombre(string $nombre, ?int $excludeId = null): bool
    {
        return $this->provinciaRepository->existeNombre($nombre, $excludeId);
    }
}
