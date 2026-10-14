<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\UsuarioModelo;
use Throwable;

/**
 * Clase UsuarioControlador
 *
 * Gestiona el control de acceso y administración de usuarios del sistema.
 * Implementa control de acceso basado en roles (RBAC) restringido a administradores.
 */
class UsuarioControlador
{
    /** Ruta base del proyecto para redirecciones */
    private const BASE_URL = '/docencia_qr';

    /** Directorio físico donde se ubican las vistas */
    private const VISTAS_DIR = APP_DIR . '/vistas';

    private UsuarioModelo $modelo;

    /**
     * Constructor del controlador con guardia RBAC.
     * 
     * CRÍTICO: Verifica que el usuario tenga rol de administrador ('admin' o 'Administrador').
     * Si no cumple la condición, deniega el acceso con HTTP 403 o redirige al dashboard.
     *
     * @param UsuarioModelo|null $modelo Instancia inyectable del modelo.
     */
    public function __construct(?UsuarioModelo $modelo = null)
    {
        $this->modelo = $modelo ?? new UsuarioModelo();

        // Verificar sesión activa
        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . self::BASE_URL . '/login');
            exit();
        }

        // ── Guardia RBAC Estricta ───────────────────────────────────────────
        $rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
        if ($rol !== 'admin' && $rol !== 'administrador') {
            // Si la petición es asíncrona (Fetch / AJAX)
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'Acceso denegado: Se requieren permisos de administrador institucional.',
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            // Acceso denegado en navegación directa
            header('Location: ' . self::BASE_URL . '/dashboard');
            exit();
        }
    }

    /**
     * GET /usuarios → Lista todos los usuarios del sistema y renderiza la vista.
     */
    public function index(): void
    {
        $usuarios = $this->modelo->listarUsuarios();

        $vistaUsuarios = self::VISTAS_DIR . '/usuarios.php';

        if (!file_exists($vistaUsuarios)) {
            http_response_code(500);
            die('[ISPEB Error] Vista no encontrada en: ' . $vistaUsuarios);
        }

        require $vistaUsuarios;
    }

    /**
     * POST /usuarios o POST /usuarios/crear → Registra un nuevo usuario en la BD.
     */
    public function guardar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $nombre   = trim((string) ($_POST['nombre']   ?? ''));
            $correo   = trim((string) ($_POST['correo']   ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $rol      = trim((string) ($_POST['rol']      ?? 'estandar'));

            // 1. Validaciones mínimas de entrada
            if ($nombre === '' || $correo === '' || $password === '') {
                http_response_code(422);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'Todos los campos (nombre, correo y contraseña) son obligatorios.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                http_response_code(422);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'El formato del correo electrónico institucional no es válido.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            if (strlen($password) < 6) {
                http_response_code(422);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'La contraseña debe tener un mínimo de 6 caracteres.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // 2. Normalización de rol ('admin' o 'estandar')
            $rolNormalizado = (strtolower($rol) === 'admin' || strtolower($rol) === 'administrador')
                ? 'admin'
                : 'estandar';

            // 3. Encriptación de contraseña mediante Bcrypt seguro
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // 4. Persistir en la base de datos a través del modelo
            $resultado = $this->modelo->crearUsuario($nombre, $correo, $passwordHash, $rolNormalizado);

            if (!$resultado['exito']) {
                http_response_code(409);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => $resultado['error'] ?? 'No se pudo crear el usuario.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            http_response_code(201);
            echo json_encode([
                'exito'   => true,
                'mensaje' => "Usuario '{$nombre}' creado correctamente con rol '{$rolNormalizado}'.",
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioControlador::guardar Error] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Ocurrió un error interno en el servidor al crear el usuario.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * GET /api/usuarios/obtener?id=...
     * Retorna la información de un usuario institucional en formato JSON para el modal de edición.
     */
    public function obtenerAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rolActual = strtolower(trim((string)($_SESSION['rol'] ?? '')));
        if (!isset($_SESSION['rol']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'No tienes permisos para editar usuarios.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Identificador de usuario inválido.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $usuario = $this->modelo->buscarPorId($id);
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Usuario no encontrado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'estado'  => 'ok',
            'exito'   => true,
            'usuario' => $usuario,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * POST /api/usuarios/actualizar
     * Actualiza la información y rol del usuario (restringido estrictamente a Administradores).
     */
    public function actualizarAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $rolActual = strtolower(trim((string)($_SESSION['rol'] ?? '')));
        if (!isset($_SESSION['rol']) || ($rolActual !== 'admin' && $rolActual !== 'administrador')) {
            echo json_encode(['estado' => 'error', 'mensaje' => 'No tienes permisos para editar usuarios.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id       = (int) ($_POST['id_usuario'] ?? $_POST['id'] ?? 0);
        $nombre   = trim((string) ($_POST['nombre']   ?? ''));
        $correo   = trim((string) ($_POST['correo']   ?? ''));
        $rol      = trim((string) ($_POST['rol']      ?? 'estandar'));
        $password = (string) ($_POST['password'] ?? '');
        $estado   = trim((string) ($_POST['estado']   ?? 'Activo'));

        if ($id <= 0 || $nombre === '' || $correo === '') {
            http_response_code(422);
            echo json_encode(['estado' => 'error', 'mensaje' => 'El nombre y correo son obligatorios.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(['estado' => 'error', 'mensaje' => 'El formato del correo electrónico no es válido.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $res = $this->modelo->actualizarUsuario($id, $nombre, $correo, $rol, $password, $estado);

        if (!$res['exito']) {
            http_response_code(409);
            echo json_encode(['estado' => 'error', 'mensaje' => $res['error'] ?? 'No se pudo actualizar el usuario.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'estado'  => 'ok',
            'exito'   => true,
            'mensaje' => 'Usuario actualizado correctamente.',
            'usuario' => [
                'id_usuario' => $id,
                'nombre'     => $nombre,
                'correo'     => $correo,
                'rol'        => $rol,
                'estado'     => $estado
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
