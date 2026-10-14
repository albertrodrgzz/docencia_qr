<?php
declare(strict_types=1);

namespace App\Controladores;

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
}
