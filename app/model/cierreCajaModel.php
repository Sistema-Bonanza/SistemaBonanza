<?php
/**
 * app/model/cierreCajaModel.php
 *
 * Guarda y consulta los cierres de caja efectuados (tabla cierres_caja, ver
 * Documentos/migracion_cierres_caja.sql). Es el ÚNICO modelo de Reportes que escribe:
 * reporteModel sigue siendo solo de lectura.
 *
 * Un cierre = un día (fecha_cierre es UNIQUE). Una vez guardado no se edita ni se borra:
 * por eso aquí no hay actualizar() ni eliminar().
 * Consultas preparadas reales: ningún parámetro con nombre se repite.
 */
class CierreCajaModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /** El cierre de un día ('Y-m-d') con el nombre de quien lo hizo, o [] si ese día no está cerrado. */
    public function obtener(string $dia): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.*, u.username
               FROM cierres_caja c
              INNER JOIN usuarios u ON u.id = c.id_usuario
              WHERE c.fecha_cierre = :dia"
        );
        $stmt->bindValue(':dia', $dia, PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        return $fila ?: [];
    }

    /** Cierres efectuados entre dos días (inclusive), indexados por fecha: ['2026-10-28' => [...]]. */
    public function porRango(string $desde, string $hasta): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT c.fecha_cierre, c.creado_en, c.diferencia, u.username
               FROM cierres_caja c
              INNER JOIN usuarios u ON u.id = c.id_usuario
              WHERE c.fecha_cierre >= :desde AND c.fecha_cierre <= :hasta"
        );
        $stmt->bindValue(':desde', $desde, PDO::PARAM_STR);
        $stmt->bindValue(':hasta', $hasta, PDO::PARAM_STR);
        $stmt->execute();

        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $mapa[$f['fecha_cierre']] = $f;
        }
        return $mapa;
    }

    /**
     * Guarda un cierre. $d: dia, facturas, total, cobrado, efectivo_sistema,
     * efectivo_contado, diferencia (decimales como texto "12.34"), observaciones (o null), id_usuario.
     * Devuelve ['ok' => true] | ['ok' => false, 'motivo' => 'duplicado'|'error'].
     * 'duplicado' = ese día ya estaba cerrado (también si dos personas cierran a la vez).
     */
    public function registrar(array $d): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO cierres_caja
                    (fecha_cierre, facturas, total_vendido, total_cobrado,
                     efectivo_sistema, efectivo_contado, diferencia, observaciones, id_usuario)
                 VALUES (:dia, :facturas, :total, :cobrado,
                         :sistema, :contado, :diferencia, :obs, :usuario)"
            );
            $stmt->bindValue(':dia',        $d['dia'], PDO::PARAM_STR);
            $stmt->bindValue(':facturas',   (int) $d['facturas'], PDO::PARAM_INT);
            $stmt->bindValue(':total',      $d['total'], PDO::PARAM_STR);
            $stmt->bindValue(':cobrado',    $d['cobrado'], PDO::PARAM_STR);
            $stmt->bindValue(':sistema',    $d['efectivo_sistema'], PDO::PARAM_STR);
            $stmt->bindValue(':contado',    $d['efectivo_contado'], PDO::PARAM_STR);
            $stmt->bindValue(':diferencia', $d['diferencia'], PDO::PARAM_STR);
            $stmt->bindValue(':obs',        $d['observaciones'], $d['observaciones'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':usuario',    (int) $d['id_usuario'], PDO::PARAM_INT);
            $stmt->execute();

            return ['ok' => true];
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                return ['ok' => false, 'motivo' => 'duplicado'];
            }
            error_log('CierreCajaModel::registrar -> ' . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error'];
        }
    }
}
