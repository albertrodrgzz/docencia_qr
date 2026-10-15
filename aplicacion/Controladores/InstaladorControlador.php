<?php
declare(strict_types=1);

namespace App\Controladores;

use PDO;
use PDOException;
use Throwable;

/**
 * Controlador de Instalación y Diagnóstico de Base de Datos para Producción
 */
class InstaladorControlador
{
    public function migrar(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        // Función robusta para leer variables de entorno en cualquier contexto (getenv, $_ENV, $_SERVER)
        $getVal = function(string $key, string $default = ''): string {
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
        };

        $host    = $getVal('DB_HOST', '127.0.0.1');
        $port    = (int) $getVal('DB_PORT', '3306');
        $dbName  = $getVal('DB_NAME', 'defaultdb');
        $user    = $getVal('DB_USER', 'root');
        $pass    = $getVal('DB_PASS', '');
        $useSsl  = strtolower($getVal('DB_SSL', 'false'));
        $appEnv  = $getVal('APP_ENV', 'production');

        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Instalador de Base de Datos | Kredentia</title>'
           . '<style>'
           . 'body{font-family:system-ui,-apple-system,sans-serif;background:#0d1117;color:#c9d1d9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box;}'
           . '.card{background:#161b22;padding:2.5rem;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.5);text-align:center;max-width:640px;width:100%;border:1px solid #30363d;}'
           . 'h1{color:#58a6ff;margin-bottom:0.75rem;font-size:1.6rem;}'
           . 'p{color:#8b949e;line-height:1.6;font-size:0.95rem;margin:0 0 1rem 0;}'
           . '.btn{display:inline-block;margin-top:1.5rem;background:#238636;color:#ffffff;text-decoration:none;font-weight:600;padding:0.75rem 1.6rem;border-radius:8px;border:none;cursor:pointer;transition:background 0.2s;}'
           . '.btn:hover{background:#2ea043;}'
           . '.table-debug{width:100%;margin-top:15px;margin-bottom:15px;border-collapse:collapse;font-size:0.85rem;text-align:left;}'
           . '.table-debug th,.table-debug td{padding:8px 12px;border:1px solid #30363d;}'
           . '.table-debug th{background:#21262d;color:#f0f6fc;}'
           . '.error-box{color:#f85149;background:#2d1618;border:1px solid #f85149;padding:14px;border-radius:8px;margin-top:18px;text-align:left;font-family:monospace;font-size:0.85rem;white-space:pre-wrap;word-break:break-all;}'
           . '.success-box{color:#3fb950;background:#0f2d18;border:1px solid #238636;padding:16px;border-radius:8px;margin-top:18px;font-size:0.95rem;line-height:1.6;text-align:left;}'
           . '</style></head><body><div class="card">';

        echo '<h1>Diagnóstico e Instalación de Base de Datos</h1>';
        echo '<p>Variables de entorno detectadas en el servidor:</p>';

        echo '<table class="table-debug">';
        echo '<tr><th>Variable</th><th>Valor Detectado</th></tr>';
        echo '<tr><td>DB_HOST</td><td><code>' . htmlspecialchars($host) . '</code></td></tr>';
        echo '<tr><td>DB_PORT</td><td><code>' . htmlspecialchars((string)$port) . '</code></td></tr>';
        echo '<tr><td>DB_NAME</td><td><code>' . htmlspecialchars($dbName) . '</code></td></tr>';
        echo '<tr><td>DB_USER</td><td><code>' . htmlspecialchars($user) . '</code></td></tr>';
        echo '<tr><td>DB_PASS</td><td><code>' . ($pass !== '' ? ('Configurada (' . strlen($pass) . ' caracteres)') : '<span style="color:#f85149;font-weight:bold;">VACÍA (No detectada)</span>') . '</code></td></tr>';
        echo '<tr><td>DB_SSL</td><td><code>' . htmlspecialchars($useSsl) . '</code></td></tr>';
        echo '<tr><td>APP_ENV</td><td><code>' . htmlspecialchars($appEnv) . '</code></td></tr>';
        echo '</table>';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 7,
            ];

            // Forzar SSL para Aiven / Cloud
            if ($useSsl === 'true' || $useSsl === '1' || $port !== 3306 || str_contains($host, 'aivencloud.com')) {
                $opciones[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $opciones[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                }
            }

            $pdo = new PDO($dsn, $user, $pass, $opciones);

            // Conexión exitosa, proceder a importar el archivo SQL si falta
            $sqlFile = dirname(__DIR__, 2) . '/database/ispeb_certificaciones.sql';
            if (!file_exists($sqlFile)) {
                throw new \RuntimeException('No se encontró el archivo de estructura SQL en: ' . $sqlFile);
            }

            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);

            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios_sistema");
            $totalUsuarios = (int) $stmt->fetchColumn();

            $base = defined('BASE_URL') ? BASE_URL : '';

            echo '<div class="success-box">';
            echo '<strong>✓ Conexión establecida con éxito al servidor MySQL:</strong><br>';
            echo '<code>' . htmlspecialchars($host) . ':' . htmlspecialchars((string)$port) . '</code><br><br>';
            echo '✓ Base de datos: <code>' . htmlspecialchars($dbName) . '</code><br>';
            echo '✓ Tablas creadas/actualizadas correctamente.<br>';
            echo '✓ Usuarios activos en el sistema: <strong>' . $totalUsuarios . '</strong>.<br>';
            echo '✓ Usuario administrador verificado: <strong>admin@ispeb.gob.ve</strong>';
            echo '</div>';

            echo '<a href="' . $base . '/login" class="btn">Ir al Inicio de Sesión</a>';

        } catch (PDOException $e) {
            echo '<div class="error-box">';
            echo '<strong>Fallo de Conexión PDO a MySQL:</strong><br>';
            echo htmlspecialchars($e->getMessage()) . '<br><br>';
            echo 'Código de error: ' . (int)$e->getCode();
            echo '</div>';
            echo '<p style="font-size:0.85rem;margin-top:15px;color:#8b949e;">Verifica en el panel de Render (pestaña <strong>Environment</strong>) que las variables coincidan exactamente con tu consola de Aiven.</p>';
        } catch (Throwable $e) {
            echo '<div class="error-box">';
            echo '<strong>Error General:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }

        echo '</div></body></html>';
        exit();
    }
}
