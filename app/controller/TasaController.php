<?php
/**
 * app/controller/TasaController.php
 *
 * Tasa de cambio oficial (BCV) para los módulos de Compras y Ventas.
 * Las dos acciones responden JSON (se usan con fetch desde assets/js/tasaBcv.js):
 *   obtener        GET   devuelve la tasa de hoy (la consulta al servicio si todavía no está guardada)
 *   guardarManual  POST  el administrador escribe la tasa a mano (si el servicio externo no responde)
 */
require_once __DIR__ . '/../helpers/seguridad.php';
require_once __DIR__ . '/../helpers/validaciones.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../model/tasaModel.php';

class TasaController
{
    private $tasaModel;

    public function __construct()
    {
        $this->tasaModel = new TasaModel((new Database())->getConnection());
    }

    public function obtener(): void
    {
        exigirSesion();
        evitarCache();
        responderJson($this->respuesta($this->tasaModel->obtenerHoy()));
    }

    public function guardarManual(): void
    {
        exigirRol(['admin']);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !validarCsrf($_POST['csrf_token'] ?? null)) {
            responderJson(['ok' => false, 'error' => 'La solicitud expiró. Recarga la página.'], 403);
        }

        // Hasta 6 enteros y 4 decimales (el BCV publica 4 decimales).
        $tasa = validarDecimal($_POST['tasa'] ?? null, 6, 4, false);
        if ($tasa === null) {
            responderJson(['ok' => false, 'error' => 'Escribe una tasa válida mayor que 0 (usa punto decimal, máximo 4 decimales).'], 422);
        }

        if (!$this->tasaModel->guardarManual($tasa, usuarioActualId())) {
            responderJson(['ok' => false, 'error' => 'No se pudo guardar la tasa. Inténtalo de nuevo.'], 500);
        }

        responderJson($this->respuesta($this->tasaModel->obtenerHoy()));
    }

    /** Forma común de la respuesta JSON. */
    private function respuesta(array $t): array
    {
        $mensaje = '';
        if ($t['estado'] === 'anterior') {
            $mensaje = 'No se pudo consultar la tasa de hoy. Se usa la última guardada (' . $t['fecha'] . ').';
        } elseif ($t['estado'] === 'sin_datos') {
            $mensaje = 'No hay tasa disponible. ' . (tieneRol(['admin']) ? 'Puedes escribirla manualmente.' : 'Pide al administrador que la registre.');
        }

        return [
            'ok'       => $t['tasa'] !== null,
            'tasa'     => $t['tasa'],
            'fecha'    => $t['fecha'],
            'fuente'   => $t['fuente'],
            'manual'   => $t['manual'],
            'estado'   => $t['estado'],
            'mensaje'  => $mensaje,
            'puedeFijar' => tieneRol(['admin']),
        ];
    }
}
