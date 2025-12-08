<?php

namespace App\Services;

use App\Repositories\TelegramaRepository;
use App\Models\Telegrama;
use Illuminate\Database\Eloquent\Collection;

/**
 * TelegramaService
 * 
 * Responsabilidad:
 * - Orquestar los casos de uso relacionados con telegramas
 * - Coordinar llamadas hacia el Modelo y el Repository
 */
class TelegramaService
{
    private TelegramaRepository $telegramaRepository;

    public function __construct(TelegramaRepository $telegramaRepository)
    {
        $this->telegramaRepository = $telegramaRepository;
    }

    /**
     * Caso de uso: Listar todos los telegramas
     */
    public function listarTelegramas(): Collection
    {
        return $this->telegramaRepository->obtenerTodos();
    }

    /**
     * Caso de uso: Obtener un telegrama por ID
     */
    public function obtenerTelegrama(int $id): ?Telegrama
    {
        return $this->telegramaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Registrar un nuevo telegrama
     */
    public function registrarTelegrama(array $datos): Telegrama
    {
        // 1. Crear el modelo de dominio
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

        // 2. Aplicar reglas de negocio
        $telegrama->verificarQueSeaValido($this->telegramaRepository);

        // 3. Guardar
        $this->telegramaRepository->guardar($telegrama);

        // 4. Retornar con relaciones
        return $this->telegramaRepository->buscarPorId($telegrama->id);
    }

    /**
     * Caso de uso: Actualizar un telegrama existente
     */
    public function actualizarTelegrama(int $id, array $datos): Telegrama
    {
        // 1. Buscar el telegrama
        $telegrama = $this->telegramaRepository->buscarPorId($id);

        if (!$telegrama) {
            throw new \Exception("Telegrama no encontrado con ID: {$id}");
        }

        // 2. Actualizar datos
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

        // 3. Aplicar reglas de negocio
        $telegrama->verificarQueSeaValido($this->telegramaRepository, $id);

        // 4. Guardar cambios
        $this->telegramaRepository->actualizar($telegrama);

        // 5. Retornar actualizado
        return $this->telegramaRepository->buscarPorId($id);
    }

    /**
     * Caso de uso: Eliminar un telegrama
     */
    public function eliminarTelegrama(int $id): array
    {
        // 1. Buscar el telegrama
        $telegrama = $this->telegramaRepository->buscarPorId($id);

        if (!$telegrama) {
            throw new \Exception("Telegrama no encontrado con ID: {$id}");
        }

        // 2. Eliminar (no hay restricciones adicionales en este caso)
        $this->telegramaRepository->eliminar($id);

        return ['mensaje' => 'Telegrama eliminado correctamente'];
    }

    /**
     * Caso de uso: Importar telegramas desde CSV o JSON
     */
    public function importarTelegramas(array $datos, string $formato): array
    {
        $errores = [];
        $telegramasValidos = [];
        $linea = 1;

        // 1. Validar cada registro
        foreach ($datos as $dato) {
            $linea++;

            // Validar estructura
            $erroresValidacion = Telegrama::validarEstructuraImportacion($dato);

            if (!empty($erroresValidacion)) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => $erroresValidacion
                ];
                continue;
            }

            // Validar que exista la mesa
            $mesa = \App\Models\Mesa::find($dato['mesa_id']);
            if (!$mesa) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["La mesa con ID {$dato['mesa_id']} no existe"]
                ];
                continue;
            }

            // Validar que exista la lista
            $lista = \App\Models\Lista::find($dato['lista_id']);
            if (!$lista) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["La lista con ID {$dato['lista_id']} no existe"]
                ];
                continue;
            }

            // Validar que no exista telegrama duplicado
            if ($this->telegramaRepository->existeTelegramaParaMesaYLista($dato['mesa_id'], $dato['lista_id'])) {
                $errores[] = [
                    'linea' => $linea,
                    'errores' => ["Ya existe un telegrama para la mesa {$dato['mesa_id']} y lista {$dato['lista_id']}"]
                ];
                continue;
            }

            // Agregar a telegramas válidos
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

        // 2. Si hay errores, no insertar nada
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

        // 3. Insertar todos los telegramas válidos en lote
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

    /**
     * Caso de uso: Listar telegramas con filtros
     */
    public function listarTelegramasConFiltros(?int $provinciaId, ?string $cargo, ?int $listaId, ?int $mesaDesde, ?int $mesaHasta): Collection
    {
        return $this->telegramaRepository->obtenerConFiltros($provinciaId, $cargo, $listaId, $mesaDesde, $mesaHasta);
    }
}
