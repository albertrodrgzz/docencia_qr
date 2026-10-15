<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\UsuarioModelo;
use Throwable;

/**
 * Clase AuthControlador
 * 
 * Gestiona el flujo de autenticación: mostrar login, procesar credenciales y cerrar sesión.
 */
class AuthControlador
{
    /** Ruta base del proyecto para redirecciones (subdirectorio Laragon) */
    private const BASE_URL = '/docencia_qr';

    /** Ruta física de las vistas */
    private const VISTAS_DIR = APP_DIR . '/vistas';

    // ────────────────────────────────────────────────────────────────────────
    // Métodos públicos del controlador
    // ────────────────────────────────────────────────────────────────────────

    /**
     * GET / → Redirige al login si no hay sesión activa, o al dashboard si la hay.
     */
    public function redirigirLogin(): void
    {
        if ($this->sesionActiva()) {
            $this->redirigir('/dashboard');
        }
        $this->redirigir('/login');
    }

    /**
     * GET /login → Renderiza la vista del formulario de login.
     */
    public function mostrarLogin(): void
    {
        // Si ya hay sesión activa, no tiene sentido volver al login
        if ($this->sesionActiva()) {
            $this->redirigir('/dashboard');
        }

        $vistaLogin = self::VISTAS_DIR . '/login.php';

        if (!file_exists($vistaLogin)) {
            http_response_code(500);
            die('[ISPEB] Vista de login no encontrada en: ' . $vistaLogin);
        }

        require $vistaLogin;
    }

    /**
     * POST /login → Procesa credenciales vía Fetch API y responde con JSON.
     * 
     * Respuesta JSON exitosa:
     *   {"exito": true, "rol": "Administrador", "redirigir": "/docencia_qr/dashboard"}
     * 
     * Respuesta JSON de fallo:
     *   {"exito": false, "mensaje": "Credenciales incorrectas o usuario inactivo."}
     */
    public function procesarLogin(): void
    {
        // Forzar cabecera JSON siempre para que Fetch API pueda parsear
        header('Content-Type: application/json; charset=utf-8');

        try {
            $correo   = trim((string) ($_POST['correo']   ?? ''));
            $password = trim((string) ($_POST['password'] ?? ''));

            // Validación mínima de campos requeridos
            if ($correo === '' || $password === '') {
                http_response_code(422);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'El correo y la contraseña son obligatorios.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $modelo   = new UsuarioModelo();
            $usuario  = $modelo->autenticar($correo, $password);

            if ($usuario === false) {
                http_response_code(401);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'Credenciales incorrectas o cuenta inactiva.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Autenticación exitosa: regenerar ID de sesión para prevenir session fixation
            session_regenerate_id(true);

            // Guardar datos de usuario en sesión (nunca el hash de contraseña)
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['correo']     = $usuario['correo'];
            $_SESSION['rol']        = $usuario['rol'];
            $_SESSION['auth']       = true;
            $_SESSION['login_at']   = time();

            $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;

            http_response_code(200);
            echo json_encode([
                'exito'     => true,
                'rol'       => $usuario['rol'],
                'redirigir' => $base . '/dashboard',
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            error_log(sprintf('[AuthControlador::procesarLogin Error] %s', $e->getMessage()));
            http_response_code(500);
            $msg = (getenv('APP_ENV') === 'local' || getenv('APP_ENV') === 'development')
                ? $e->getMessage()
                : 'Error interno del servidor. Intente nuevamente.';
            echo json_encode([
                'exito'   => false,
                'mensaje' => $msg,
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /logout → Destruye la sesión y redirige al login.
     */
    public function cerrarSesion(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Limpiar todos los datos de sesión
            $_SESSION = [];

            // Destruir la cookie de sesión del navegador
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            session_destroy();
        }

        $this->redirigir('/login');
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
     * @param string $ruta Ruta relativa (ej: '/dashboard').
     */
    private function redirigir(string $ruta): void
    {
        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;
        header('Location: ' . $base . $ruta);
        exit();
    }
}
