<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\FuncionarioModelo;

/**
 * Clase DashboardControlador
 *
 * Gestiona el acceso al panel de control principal de la aplicación.
 * Protege la ruta verificando que exista una sesión autenticada activa.
 */
class DashboardControlador
{
    /** Ruta base del proyecto para redirecciones (subdirectorio Laragon) */
    private const BASE_URL = '/docencia_qr';

    /** Ruta física de las vistas */
    private const VISTAS_DIR = APP_DIR . '/vistas';

    // ────────────────────────────────────────────────────────────────────────
    // Métodos públicos del controlador
    // ────────────────────────────────────────────────────────────────────────

    /**
     * GET /dashboard → Renderiza el panel de control si hay sesión activa.
     *
     * Si la sesión no es válida, redirige inmediatamente a la raíz (/).
     */
    public function index(): void
    {
        // Guardia de autenticación: si no hay sesión activa, redirigir al login
        if (!$this->sesionActiva()) {
            $this->redirigir('/');
        }

        // Obtener estadísticas reales de funcionarios desde el modelo
        $funcionarioModelo = new FuncionarioModelo();
        $datos = $funcionarioModelo->obtenerEstadisticas();

        $vistaDashboard = self::VISTAS_DIR . '/dashboard.php';

        if (!file_exists($vistaDashboard)) {
            http_response_code(500);
            die('[ISPEB] Vista del dashboard no encontrada en: ' . $vistaDashboard);
        }

        require $vistaDashboard;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Métodos privados de apoyo
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Verifica si existe una sesión de usuario autenticado activa.
     */
    private function sesionActiva(): bool
    {
        return !empty($_SESSION['auth']) && !empty($_SESSION['id_usuario']);
    }

    /**
     * Emite una redirección HTTP 302 hacia una ruta relativa del sistema.
     *
     * @param string $ruta Ruta relativa (ej: '/login').
     */
    private function redirigir(string $ruta): void
    {
        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;
        header('Location: ' . $base . $ruta);
        exit();
    }
}
