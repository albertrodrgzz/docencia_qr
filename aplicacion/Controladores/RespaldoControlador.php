<?php
declare(strict_types=1);

namespace App\Controladores;

/**
 * Clase RespaldoControlador
 *
 * Gestiona el módulo operativo independiente de respaldos institucionales.
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
     * Guardia de seguridad RBAC: Solo administradores pueden ingresar al módulo de respaldos.
     */
    private function verificarPermisosAdmin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . self::BASE_URL . '/login');
            exit();
        }

        $rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
        if ($rol !== 'admin' && $rol !== 'administrador') {
            header('Location: ' . self::BASE_URL . '/dashboard');
            exit();
        }
    }
}
