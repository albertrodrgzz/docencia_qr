<?php
declare(strict_types=1);

namespace App\Controladores;

use Configuracion\ConexionBD;
use Throwable;

/**
 * Controlador de Instalación y Migración Inicial para Despliegues en la Nube
 */
class InstaladorControlador
{
    /**
     * GET /instalar-bd
     * Inicializa las tablas e inserta los usuarios iniciales en la base de datos conectada.
     */
    public function migrar(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Instalador de Base de Datos | Kredentia</title>'
           . '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0d1117;color:#c9d1d9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box;}'
           . '.card{background:#161b22;padding:2.5rem;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.5);text-align:center;max-width:550px;width:100%;border:1px solid #30363d;}'
           . 'h1{color:#58a6ff;margin-bottom:1rem;font-size:1.6rem;}p{color:#8b949e;line-height:1.6;font-size:0.95rem;}'
           . '.btn{display:inline-block;margin-top:1.5rem;background:#238636;color:#ffffff;text-decoration:none;font-weight:600;padding:0.75rem 1.5rem;border-radius:8px;border:none;cursor:pointer;}'
           . '.btn:hover{background:#2ea043;}'
           . '.error{color:#f85149;background:#2d1618;border:1px solid #f85149;padding:12px;border-radius:8px;margin-top:15px;text-align:left;font-family:monospace;font-size:0.85rem;white-space:pre-wrap;}'
           . '.success{color:#3fb950;background:#0f2d18;border:1px solid #238636;padding:15px;border-radius:8px;margin-top:15px;font-size:0.95rem;line-height:1.5;}'
           . '</style></head><body><div class="card">';

        try {
            $pdo = ConexionBD::getInstancia();
            $sqlFile = dirname(__DIR__, 2) . '/database/ispeb_certificaciones.sql';

            if (!file_exists($sqlFile)) {
                throw new \RuntimeException('No se encontró el archivo de estructura SQL en: ' . $sqlFile);
            }

            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);

            // Verificar que se crearon los usuarios
            $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios_sistema");
            $total = (int) $stmt->fetchColumn();

            $base = defined('BASE_URL') ? BASE_URL : '';

            echo '<h1>¡Base de Datos Configurada con Éxito!</h1>';
            echo '<div class="success">✓ Tablas creadas correctamente en tu base de datos.<br>✓ Estructura de funcionarios y credenciales QR lista.<br>✓ Usuarios iniciales importados (' . $total . ' usuarios activos).</div>';
            echo '<p style="margin-top:20px;">Usuario administrador listo:<br><strong style="color:#ffffff;">admin@ispeb.gob.ve</strong></p>';
            echo '<a href="' . $base . '/login" class="btn">Ir al Inicio de Sesión</a>';

        } catch (Throwable $e) {
            echo '<h1 style="color:#f85149;">Fallo de Conexión a la Base de Datos</h1>';
            echo '<p>No se pudo conectar o ejecutar la estructura en la base de datos configurada:</p>';
            echo '<div class="error">' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<p style="font-size:0.85rem;margin-top:15px;color:#8b949e;">Asegúrate de haber configurado las variables en Render (sección <strong>Environment</strong>):<br><strong>DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME</strong>.</p>';
        }

        echo '</div></body></html>';
        exit();
    }
}
