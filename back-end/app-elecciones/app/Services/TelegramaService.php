<?php

namespace App\Services;

use App\Repositories\TelegramaRepository;
use App\Models\Telegrama;
use Illuminate\Database\Eloquent\Collection;

//Servicio de orquestación de casos de uso de telegramas
class TelegramaService
{
    private TelegramaRepository $telegramaRepository;

    public function __construct(TelegramaRepository $telegramaRepository)
    {
        $this->telegramaRepository = $telegramaRepository;
    }

    //Listar todos los telegramas
    public function listarTelegramas(): Collection
    {
        return $this->telegramaRepository->obtenerTodos();
    }

    //Obtener telegrama por ID
    public function obtenerTelegrama(int $id): ?Telegrama
    {
        return $this->telegramaRepository->buscarPorId($id);
    }

    //Registrar nuevo telegrama
    public function registrarTelegrama(array $datos): Telegrama
    {
        $telegrama = Telegrama::crearDesdeRequest(
            $datos['mesa_id'],
            $datos['lista_id'],
            $datos['votos_Diputados'],
            $datos['votos_Senadores'],
            $datos['voto_Blancos'],
            $datos['voto_Nulos'],
            $datos['voto_Recurridos'],
            $datos['usuario'] ?? null
        );

        $telegrama->verificarQueSeaValido($this->telegramaRepository);
        $this->telegramaRepository->guardar($telegrama);
        return $this->telegramaRepository->buscarPorId($telegrama->id);
    }

    //Actualizar telegrama existente
    public function actualizarTelegrama(int $id, array $datos): Telegrama
    {
        $telegrama = $this->telegramaRepository->buscarPorId($id);

        if (!$telegrama) {
            throw new \Exception("Telegrama no encontrado con ID: {$id}");
        }

        $telegrama->actualizarDatos(
            $datos['mesa_id'],
            $datos['lista_id'],
            $datos['votos_Diputados'],
            $datos['votos_Senadores'],
            $datos['voto_Blancos'],
            $datos['voto_Nulos'],
            $datos['voto_Recurridos'],
            $datos['usuario'] ?? null
        );

        $telegrama->verificarQueSeaValido($this->telegramaRepository, $id);
        $this->telegramaRepository->actualizar($telegrama);
        return $this->telegramaRepository->buscarPorId($id);
    }

    //Eliminar telegrama
    public function eliminarTelegrama(int $id): array
    {
        $telegrama = $this->telegramaRepository->buscarPorId($id);

        if (!$telegrama) {
            throw new \Exception("Telegrama no encontrado con ID: {$id}");
        }

        $this->telegramaRepository->eliminar($id);

        return ['mensaje' => 'Telegrama eliminado correctamente'];
    }

    //Importar telegramas desde CSV o JSON
    public function importarTelegramas(array $datos, string $formato): array
    {
        $errores = [];
        $telegramasValidos = [];
        $linea = 1;

        foreach ($datos as $dato) {
            $linea++;

            $erroresValidacion = Telegrama::validarEstructuraImportacion($dato);

            if (!empty($erroresValidacion)) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => $erroresValidacion
                ];
                continue;
            }

            $mesa = \App\Models\Mesa::find($dato['mesa_id']);
            if (!$mesa) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["La mesa con ID {$dato['mesa_id']} no existe"]
                ];
                continue;
            }

            $lista = \App\Models\Lista::find($dato['lista_id']);
            if (!$lista) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["La lista con ID {$dato['lista_id']} no existe"]
                ];
                continue;
            }

            if ($this->telegramaRepository->existeTelegramaParaMesaYLista($dato['mesa_id'], $dato['lista_id'])) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["Ya existe un telegrama para la mesa {$dato['mesa_id']} y lista {$dato['lista_id']}"]
                ];
                continue;
            }

            $telegramasValidos[] = [
                'mesa_id' => (int) $dato['mesa_id'],
                'lista_id' => (int) $dato['lista_id'],
                'votos_Diputados' => (int) $dato['votos_Diputados'],
                'votos_Senadores' => (int) $dato['votos_Senadores'],
                'voto_Blancos' => (int) $dato['voto_Blancos'],
                'voto_Nulos' => (int) $dato['voto_Nulos'],
                'voto_Recurridos' => (int) $dato['voto_Recurridos'],
                'usuario_carga' => $dato['usuario'] ?? 'Importación',
            ];
        }

        if (!empty($errores)) {
            return [
                'exito' => false,
                'mensaje' => 'Se encontraron errores en la importación',
                'total_registros' => count($datos),
                'registros_validos' => count($telegramasValidos),
                'registros_con_errores' => count($errores),
                'errores' => $errores
            ];
        }

        if (!empty($telegramasValidos)) {
            $this->telegramaRepository->guardarLote($telegramasValidos);
        }

        return [
            'exito' => true,
            'mensaje' => 'Importación completada exitosamente',
            'total_registros' => count($datos),
            'registros_importados' => count($telegramasValidos),
            'registros_con_errores' => 0,
            'errores' => []
        ];
    }

    //Listar telegramas con filtros
    public function listarTelegramasConFiltros(?int $provinciaId, ?string $cargo, ?int $listaId, ?int $mesaDesde, ?int $mesaHasta): Collection
    {
        return $this->telegramaRepository->obtenerConFiltros($provinciaId, $cargo, $listaId, $mesaDesde, $mesaHasta);
    }
}
