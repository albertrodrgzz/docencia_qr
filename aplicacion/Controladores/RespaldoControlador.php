<?php
declare(strict_types=1);

namespace App\Controladores;

use Configuracion\ConexionBD;
use PDO;
use Throwable;

/**
 * Clase RespaldoControlador
 *
 * Gestiona el módulo operativo independiente de respaldos y restauración institucional.
 * Garantiza la separación entre la emisión de reportes y las políticas de copias de seguridad de la BD.
 */
class RespaldoControlador
{
    private const BASE_URL = '/docencia_qr';

    /**
     * GET /respaldos → Renderiza la vista de Respaldos del Sistema.
     */
    public function index(): void
    {
        $this->verificarPermisosAdmin();

        $vista = dirname(__DIR__) . '/Vistas/respaldos.php';
        if (!file_exists($vista)) {
            http_response_code(500);
            die('[ISPEB] Vista de respaldos no encontrada en: ' . $vista);
        }

        require_once $vista;
    }

    /**
     * POST /respaldos/restaurar | POST /api/respaldos/restaurar
     *
     * Tarea 2: Procesa la subida del archivo .sql, valida rol admin y restaura la BD con PDO.
     */
    public function restaurarAjax(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        // 1. Verificación estricta de sesión y rol de administrador
        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Sesión expirada o no autorizada. Inicie sesión nuevamente.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
        if ($rol !== 'admin' && $rol !== 'administrador') {
            http_response_code(403);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Acceso denegado. Se requieren privilegios de Administrador para restaurar el sistema.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // 2. Recepción y validación del archivo SQL subido
        $archivo = $_FILES['archivo_sql'] ?? $_FILES['archivo'] ?? null;
        if (!$archivo || !isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'No se recibió un archivo .sql válido o hubo un fallo durante la carga.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $nombreOriginal = (string) ($archivo['name'] ?? '');
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if ($extension !== 'sql') {
            http_response_code(400);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Formato no permitido. Solo se aceptan archivos con extensión .sql'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // 3. Lectura del contenido SQL
        $contenidoSql = file_get_contents($archivo['tmp_name']);
        if ($contenidoSql === false || trim($contenidoSql) === '') {
            http_response_code(400);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'El archivo proporcionado está vacío o no se pudo leer.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // 4. Ejecución del script SQL en la base de datos
        try {
            $pdo = ConexionBD::getInstancia();
            // Deshabilitar emulación de prepare para permitir ejecución multi-query si el driver lo soporta
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

            // Ejecución de las sentencias SQL
            $pdo->exec($contenidoSql);

            echo json_encode([
                'estado'  => 'ok',
                'exito'   => true,
                'mensaje' => 'Base de datos restaurada correctamente a partir del archivo ' . htmlspecialchars($nombreOriginal, ENT_QUOTES, 'UTF-8') . '.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            error_log(sprintf('[RespaldoControlador::restaurarAjax Error PDO] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Fallo al ejecutar las instrucciones SQL en el servidor: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Guardia de seguridad RBAC: Solo administradores pueden ingresar al módulo de respaldos.
     */
    private function verificarPermisosAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . $base . '/login');
            exit();
        }

        $rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
        if ($rol !== 'admin' && $rol !== 'administrador') {
            header('Location: ' . $base . '/dashboard');
            exit();
        }
    }
}

