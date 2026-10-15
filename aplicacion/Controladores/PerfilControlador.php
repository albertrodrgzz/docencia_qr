<?php
declare(strict_types=1);

namespace App\Controladores;

use Configuracion\ConexionBD;
use Throwable;

/**
 * Clase PerfilControlador
 *
 * Gestiona el módulo "Mi Perfil" del usuario autenticado en el sistema institucional.
 */
class PerfilControlador
{
    /**
     * GET /perfil → Muestra la vista de perfil del usuario actual.
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Guardia de autenticación: requiere sesión activa
        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            $baseDir = defined('BASE_URL') ? BASE_URL : '/docencia_qr';
            header('Location: ' . $baseDir . '/login');
            exit();
        }

        require_once dirname(__DIR__) . '/Vistas/perfil.php';
    }

    /**
     * POST /perfil/actualizar-password | POST /api/perfil/actualizar-password
     *
     * Valida la contraseña actual y actualiza las credenciales institucionales del usuario.
     */
    public function actualizarPassword(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Sesión expirada o no iniciada.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $idUsuario       = (int) $_SESSION['id_usuario'];
        $passwordActual  = trim((string) ($_POST['password_actual'] ?? ''));
        $passwordNueva   = trim((string) ($_POST['password_nueva'] ?? ''));

        if ($passwordActual === '' || $passwordNueva === '') {
            http_response_code(400);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Por favor ingrese la contraseña actual y la nueva contraseña.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (strlen($passwordNueva) < 6) {
            http_response_code(400);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'La nueva contraseña debe tener un mínimo de 6 caracteres.'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $pdo = ConexionBD::getInstancia();
            $stmt = $pdo->prepare("SELECT password_hash FROM usuarios_sistema WHERE id_usuario = :id LIMIT 1");
            $stmt->execute([':id' => $idUsuario]);
            $hashActual = $stmt->fetchColumn();

            if (!$hashActual || !password_verify($passwordActual, (string) $hashActual)) {
                http_response_code(400);
                echo json_encode([
                    'estado'  => 'error',
                    'exito'   => false,
                    'mensaje' => 'La contraseña actual ingresada es incorrecta.'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $nuevoHash = password_hash($passwordNueva, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE usuarios_sistema SET password_hash = :hash WHERE id_usuario = :id LIMIT 1");
            $upd->execute([':hash' => $nuevoHash, ':id' => $idUsuario]);

            echo json_encode([
                'estado'  => 'ok',
                'exito'   => true,
                'mensaje' => 'Contraseña institucional actualizada exitosamente.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            error_log(sprintf('[PerfilControlador::actualizarPassword Error] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'estado'  => 'error',
                'exito'   => false,
                'mensaje' => 'Ocurrió un error inesperado al actualizar las credenciales.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}

