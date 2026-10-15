<?php
declare(strict_types=1);

namespace Configuracion;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Clase ConexionBD
 * 
 * Implementa el patrón de diseño Singleton para gestionar una conexión única
 * y eficiente a la base de datos MySQL mediante PDO bajo PHP 8+.
 * Compatible con Laragon (local) y entornos Cloud / Docker (Render + Aiven.io).
 */
final class ConexionBD
{
    /**
     * Instancia única de la conexión PDO.
     */
    private static ?PDO $conexion = null;

    /**
     * Parámetros de conexión predeterminados para entorno Laragon local.
     */
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'ispeb_certificaciones';
    private const DB_CHARSET = 'utf8mb4';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    /**
     * Constructor privado para prevenir instanciación directa.
     */
    private function __construct()
    {
    }

    /**
     * Previene la clonación del objeto Singleton.
     */
    private function __clone()
    {
    }

    /**
     * Previene la deserialización del objeto Singleton.
     * 
     * @throws RuntimeException
     */
    public function __wakeup(): void
    {
        throw new RuntimeException("No está permitido deserializar una instancia de ConexionBD.");
    }

    /**
     * Obtiene el valor de una variable de entorno consultando getenv(), $_ENV y $_SERVER.
     * Garantiza compatibilidad tanto en CLI, Apache mod_php, Docker y Render.
     */
    private static function getEnvVal(string $key, string $default = ''): string
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return (string) $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string) $_SERVER[$key];
        }
        return $default;
    }

    /**
     * Obtiene la instancia única de PDO configurada estrictamente.
     * 
     * @return PDO Objeto de conexión a base de datos.
     * @throws RuntimeException Si ocurre un error al conectar con el servidor.
     */
    public static function getInstancia(): PDO
    {
        if (self::$conexion === null) {
            $host    = self::getEnvVal('DB_HOST', self::DB_HOST);
            $port    = self::getEnvVal('DB_PORT', self::DB_PORT);
            $dbName  = self::getEnvVal('DB_NAME', self::DB_NAME);
            $charset = self::getEnvVal('DB_CHARSET', self::DB_CHARSET);
            $user    = self::getEnvVal('DB_USER', self::DB_USER);
            $pass    = self::getEnvVal('DB_PASS', self::DB_PASS);
            $sslVal  = strtolower(self::getEnvVal('DB_SSL', 'false'));

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            $opciones = [
                // Forzar excepciones estrictas en caso de errores SQL
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Array asociativo como modo de recuperación estándar
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Desactivar emulación para sentencias preparadas reales del motor
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Respetar tipos de datos nativos devueltos por el controlador
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                // Timeout de 7s para prevenir bloqueos indefinidos en servidores remotos
                PDO::ATTR_TIMEOUT            => 7,
            ];

            // Soporte SSL automático para bases de datos Cloud (Aiven, PlanetScale, AWS)
            if ($sslVal === 'true' || $sslVal === '1' || (int)$port !== 3306 || str_contains($host, 'aivencloud.com')) {
                $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $opciones[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                }
            }

            try {
                self::$conexion = new PDO($dsn, $user, $pass, $opciones);
            } catch (PDOException $e) {
                // Registrar el error detallado internamente
                error_log(sprintf('[ConexionBD Error] Fallo de conexión PDO a [%s:%s/%s con usuario %s]: %s', $host, $port, $dbName, $user, $e->getMessage()));

                $appEnv = strtolower(self::getEnvVal('APP_ENV', 'local'));
                $esLocal = ($appEnv === 'local' || $appEnv === 'development' || empty($appEnv));

                // Fallback automático en entorno local si el host remoto configurado no responde o rechaza permisos
                if ($esLocal && $host !== '127.0.0.1' && $host !== 'localhost') {
                    try {
                        error_log('[ConexionBD Fallback] Activando conexión de respaldo a MySQL local 127.0.0.1...');
                        $dsnLocal = "mysql:host=127.0.0.1;port=3306;dbname=ispeb_certificaciones;charset=utf8mb4";
                        self::$conexion = new PDO($dsnLocal, 'root', '', $opciones);
                        return self::$conexion;
                    } catch (PDOException $eLocal) {
                        error_log(sprintf('[ConexionBD Fallback Error] Fallo también en MySQL local: %s', $eLocal->getMessage()));
                    }
                }

                $mensaje = $esLocal
                    ? sprintf("Error de conexión a la base de datos [%s]: %s", $host, $e->getMessage())
                    : "Error de conexión con la base de datos institucional. Por favor, contacte al administrador.";

                throw new RuntimeException(
                    $mensaje,
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$conexion;
    }

    /**
     * Alias en español para obtener la instancia PDO.
     * 
     * @return PDO
     */
    public static function obtenerConexion(): PDO
    {
        return self::getInstancia();
    }
}
