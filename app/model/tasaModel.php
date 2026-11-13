<?php
/**
 * app/model/tasaModel.php
 *
 * Tasa de cambio oficial (BCV) en bolívares por 1 USD.
 *
 * Cómo funciona (en este orden):
 *   1. Si ya hay una tasa guardada para HOY en la tabla tasas_cambio, se usa esa.
 *   2. Si no, se consulta un servicio público (FUENTES) y se guarda como la tasa de hoy.
 *      Así el servicio externo se llama UNA vez al día, no en cada pantalla.
 *   3. Si el servicio no responde, se usa la última tasa guardada y se marca como "anterior"
 *      (la pantalla avisa que no es la de hoy).
 *   4. Si no hay nada guardado, devuelve 'sin_datos' y el administrador puede escribirla a mano.
 *
 * Los servicios externos son de terceros (no del BCV). Si alguno cambia de formato o deja de
 * funcionar, solo hay que editar la lista FUENTES: el resto del sistema no se entera.
 */
class TasaModel
{
    /** Servicios que se prueban en orden. 'url' devuelve JSON; el valor se busca con extraerTasa(). */
    private const FUENTES = [
        ['nombre' => 'DolarAPI (BCV)',  'url' => 'https://ve.dolarapi.com/v1/dolares/oficial'],
        ['nombre' => 'tasa-bcv-api',    'url' => 'https://tasa-bcv-api-production.up.railway.app/v1/rates/latest'],
    ];

    /** Segundos de espera máxima por cada servicio externo. */
    private const TIMEOUT = 4;

    /** Tras un fallo, no se vuelve a consultar el servicio durante estos segundos (evita lentitud en cada página). */
    private const ESPERA_TRAS_FALLO = 300;

    /** Rango razonable de la tasa: protege contra una respuesta absurda del servicio externo. */
    private const MIN_TASA = 0.01;
    private const MAX_TASA = 100000;

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Tasa vigente para mostrar y para guardar en las operaciones.
     * Devuelve ['tasa' => float|null, 'fecha' => 'Y-m-d'|null, 'fuente' => string,
     *           'manual' => bool, 'estado' => 'hoy'|'anterior'|'sin_datos']
     */
    public function obtenerHoy(): array
    {
        $hoy = date('Y-m-d');

        $fila = $this->leer($hoy);
        if ($fila !== null) {
            return $this->resultado($fila, 'hoy');
        }

        // No hay tasa de hoy: se intenta consultar el servicio (con espera tras un fallo previo).
        $ultimoIntento = (int) ($_SESSION['tasa_ultimo_fallo'] ?? 0);
        if ((time() - $ultimoIntento) >= self::ESPERA_TRAS_FALLO) {
            $consulta = $this->consultarServicios();
            if ($consulta !== null) {
                $this->guardar($hoy, $consulta['tasa'], $consulta['fuente'], false, null);
                unset($_SESSION['tasa_ultimo_fallo']);
                return [
                    'tasa'   => (float) $consulta['tasa'],
                    'fecha'  => $hoy,
                    'fuente' => $consulta['fuente'],
                    'manual' => false,
                    'estado' => 'hoy',
                ];
            }
            $_SESSION['tasa_ultimo_fallo'] = time();
        }

        // Plan B: la última tasa guardada.
        $anterior = $this->ultimaGuardada();
        if ($anterior !== null) {
            return $this->resultado($anterior, 'anterior');
        }

        return ['tasa' => null, 'fecha' => null, 'fuente' => '', 'manual' => false, 'estado' => 'sin_datos'];
    }

    /** El administrador fija la tasa de hoy a mano. $tasa ya viene validada (texto "123.4567"). */
    public function guardarManual(string $tasa, int $idUsuario): bool
    {
        return $this->guardar(date('Y-m-d'), $tasa, 'Manual', true, $idUsuario);
    }

    // ------------------------------------------------------------------
    // Base de datos
    // ------------------------------------------------------------------

    private function leer(string $fecha): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT fecha, tasa, fuente, manual FROM tasas_cambio WHERE fecha = ?');
            $stmt->execute([$fecha]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log('TasaModel::leer -> ' . $e->getMessage());
            return null;
        }
    }

    private function ultimaGuardada(): ?array
    {
        try {
            $fila = $this->pdo->query('SELECT fecha, tasa, fuente, manual FROM tasas_cambio ORDER BY fecha DESC LIMIT 1')
                              ->fetch(PDO::FETCH_ASSOC);
            return $fila ?: null;
        } catch (PDOException $e) {
            error_log('TasaModel::ultimaGuardada -> ' . $e->getMessage());
            return null;
        }
    }

    private function guardar(string $fecha, $tasa, string $fuente, bool $manual, ?int $idUsuario): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO tasas_cambio (fecha, tasa, fuente, manual, id_usuario)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE tasa = VALUES(tasa), fuente = VALUES(fuente),
                                         manual = VALUES(manual), id_usuario = VALUES(id_usuario)'
            );
            return $stmt->execute([$fecha, number_format((float) $tasa, 4, '.', ''), $fuente, $manual ? 1 : 0, $idUsuario]);
        } catch (PDOException $e) {
            error_log('TasaModel::guardar -> ' . $e->getMessage());
            return false;
        }
    }

    private function resultado(array $fila, string $estado): array
    {
        return [
            'tasa'   => (float) $fila['tasa'],
            'fecha'  => $fila['fecha'],
            'fuente' => $fila['fuente'],
            'manual' => (int) $fila['manual'] === 1,
            'estado' => $estado,
        ];
    }

    // ------------------------------------------------------------------
    // Servicios externos
    // ------------------------------------------------------------------

    /** Prueba cada servicio hasta que uno devuelva una tasa válida. ['tasa' => float, 'fuente' => string] o null. */
    private function consultarServicios(): ?array
    {
        foreach (self::FUENTES as $fuente) {
            $cuerpo = $this->descargar($fuente['url']);
            if ($cuerpo === null) {
                continue;
            }
            $datos = json_decode($cuerpo, true);
            if (!is_array($datos)) {
                continue;
            }
            $tasa = $this->extraerTasa($datos);
            if ($tasa !== null) {
                return ['tasa' => $tasa, 'fuente' => $fuente['nombre']];
            }
        }
        return null;
    }

    /** Descarga una URL con tiempo de espera corto. Texto o null si falla. */
    private function descargar(string $url): ?string
    {
        try {
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => self::TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 2,
                    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                    CURLOPT_USERAGENT      => 'SistemaBonanza/1.0',
                ]);
                $respuesta = curl_exec($ch);
                $codigo    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                return ($respuesta !== false && $codigo >= 200 && $codigo < 300) ? (string) $respuesta : null;
            }

            if (ini_get('allow_url_fopen')) {
                $contexto  = stream_context_create(['http' => [
                    'method'  => 'GET',
                    'timeout' => self::TIMEOUT,
                    'header'  => "Accept: application/json\r\nUser-Agent: SistemaBonanza/1.0\r\n",
                ]]);
                $respuesta = @file_get_contents($url, false, $contexto);
                return $respuesta === false ? null : $respuesta;
            }
        } catch (Throwable $e) {
            error_log('TasaModel::descargar -> ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Busca la tasa dentro del JSON sin depender de un formato exacto: prueba las claves habituales
     * ('promedio', 'venta', 'tasa', 'rate', 'price', 'valor', 'usd') en cualquier nivel.
     */
    private function extraerTasa(array $datos, int $nivel = 0): ?float
    {
        if ($nivel > 4) {
            return null;
        }
        foreach (['promedio', 'venta', 'tasa', 'rate', 'price', 'valor', 'usd'] as $clave) {
            if (!array_key_exists($clave, $datos)) {
                continue;
            }
            $valor = $datos[$clave];
            if (is_array($valor)) {
                $hallado = $this->extraerTasa($valor, $nivel + 1);
                if ($hallado !== null) {
                    return $hallado;
                }
            } elseif (is_numeric($valor) && (float) $valor >= self::MIN_TASA && (float) $valor <= self::MAX_TASA) {
                return (float) $valor;
            }
        }
        if (isset($datos['data']) && is_array($datos['data'])) {
            return $this->extraerTasa($datos['data'], $nivel + 1);
        }
        return null;
    }
}
