<?php
declare(strict_types=1);

// =============================================================================
// Sistema de Certificación Institucional - ISPEB
// Front Controller (index.php) | Punto de entrada único de la aplicación
// PHP 8.1+ | Patrón: MVC + Front Controller + Router
// =============================================================================

// ── 1. Constantes del entorno ────────────────────────────────────────────────
define('APP_ROOT',     __DIR__);
define('APP_DIR',      APP_ROOT . '/aplicacion');
define('CONFIG_DIR',   APP_ROOT . '/configuracion');
define('VENDOR_DIR',   APP_ROOT . '/vendor');
define('STORAGE_DIR',  APP_ROOT . '/almacenamiento');
define('BASE_URL',     '/docencia_qr');
define('VERSION',      '1.0.0');

// ── 1.1 Carga segura de variables de entorno (.env) ──────────────────────────
$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lineasEnv = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lineasEnv !== false) {
        foreach ($lineasEnv as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || str_starts_with($linea, ';')) {
                continue;
            }
            if (str_contains($linea, '=')) {
                [$clave, $valor] = explode('=', $linea, 2);
                $clave = trim($clave);
                $valor = trim($valor, " \t\n\r\0\x0B\"'");
                putenv("{$clave}={$valor}");
                $_ENV[$clave] = $valor;
                $_SERVER[$clave] = $valor;
            }
        }
    }
}

// ── 1.2 Helper global anti-XSS ───────────────────────────────────────────────
if (!function_exists('sanitizar')) {
    function sanitizar(mixed $valor): string
    {
        return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
if (!function_exists('e')) {
    function e(mixed $valor): string
    {
        return sanitizar($valor);
    }
}

// ── 2. Autoloader de Composer ────────────────────────────────────────────────
$autoload = VENDOR_DIR . '/autoload.php';

if (!file_exists($autoload)) {
    http_response_code(503);
    die('[ISPEB] Las dependencias de la aplicación no están instaladas. Ejecute: composer install');
}

require_once $autoload;

// ── 3. Configuración de errores según entorno ────────────────────────────────
$esProduccion = (getenv('APP_ENV') === 'production');

error_reporting(E_ALL);

// Carpeta de registros protegida
$dirLogs = STORAGE_DIR . '/logs';
if (!is_dir($dirLogs)) {
    @mkdir($dirLogs, 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $dirLogs . '/error.log');

if ($esProduccion) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');

    // Manejador global de excepciones limpio en producción
    set_exception_handler(function (\Throwable $e) {
        error_log(sprintf(
            "[EXCEPCIÓN CRÍTICA] %s en %s:%d\nStack Trace:\n%s",
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        if (!headers_sent()) {
            http_response_code(500);
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Error interno en el servidor. Por favor, contacte al soporte técnico.'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        die('<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error del Sistema | Kredentia</title>'
          . '<style>body{font-family:system-ui,-apple-system,sans-serif;background:#0d1117;color:#c9d1d9;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}'
          . '.card{background:#161b22;padding:2.5rem;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.5);text-align:center;max-width:460px;border:1px solid #30363d;}'
          . 'h1{color:#f85149;margin-bottom:0.5rem;font-size:1.5rem;}p{color:#8b949e;line-height:1.5;font-size:0.95rem;}'
          . 'a{display:inline-block;margin-top:1.5rem;color:#58a6ff;text-decoration:none;font-weight:500;border:1px solid #30363d;padding:0.6rem 1.2rem;border-radius:8px;}'
          . 'a:hover{background:#21262d;}</style></head><body><div class="card">'
          . '<h1>500 | Error del Sistema</h1><p>El servicio experimentó un inconveniente inesperado. El evento ha sido registrado internamente para su pronta resolución.</p>'
          . '<a href="/docencia_qr/login">Volver al inicio</a></div></body></html>');
    });
} else {
    // Entorno local / desarrollo: muestra errores para depuración rápida
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

// ── 4. Inicio de sesión seguro ───────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
               || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
               || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,           // Sesión expira al cerrar el navegador
        'path'     => '/',
        'domain'   => '',
        'secure'   => $esHttps,    // Seguro automáticamente en HTTPS
        'httponly' => true,        // Previene acceso desde JavaScript (XSS)
        'samesite' => 'Strict',    // Protege contra CSRF
    ]);
    session_name('ISPEB_SESS');
    session_start();

    // Regenerar ID de sesión para prevenir session fixation
    if (empty($_SESSION['_token_iniciado'])) {
        session_regenerate_id(true);
        $_SESSION['_token_iniciado'] = true;
    }
}

// ── 5. Carga de configuración y conexión a BD ────────────────────────────────
require_once CONFIG_DIR . '/ConexionBD.php';

// ── 6. Enrutador: captura y parseo de la URL solicitada ──────────────────────
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';

// Eliminar el prefijo del subdirectorio si la app corre en una subcarpeta
$baseDir     = '/docencia_qr';
if (str_starts_with($requestUri, $baseDir)) {
    $requestUri = substr($requestUri, strlen($baseDir));
}
$requestUri  = $requestUri ?: '/';

// Separar ruta de query string y normalizar
$rutaLimpia  = parse_url($requestUri, PHP_URL_PATH);
$rutaLimpia  = rtrim($rutaLimpia ?: '/', '/') ?: '/';
$metodo      = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// ── 7. Tabla de rutas ────────────────────────────────────────────────────────
// Estructura: 'MÉTODO:/ruta' => ['Controlador', 'metodoAccion']
//
// Los controladores viven en aplicacion/controladores/ y siguen PSR-4:
// App\Controladores\NombreControlador

$rutas = [
    'GET:/'                  => ['App\\Controladores\\AuthControlador',         'redirigirLogin'],
    'GET:/login'             => ['App\\Controladores\\AuthControlador',         'mostrarLogin'],
    'POST:/login'            => ['App\\Controladores\\AuthControlador',         'procesarLogin'],
    'GET:/logout'            => ['App\\Controladores\\AuthControlador',         'cerrarSesion'],
    'GET:/dashboard'         => ['App\\Controladores\\DashboardControlador',    'index'],
    'GET:/funcionarios'      => ['App\\Controladores\\FuncionarioControlador',  'index'],
    'POST:/funcionarios/guardar' => ['App\\Controladores\\FuncionarioControlador',  'registrarManual'],
    'POST:/registro-manual'  => ['App\\Controladores\\FuncionarioControlador',  'registrarManual'],
    'POST:/registro-excel'   => ['App\\Controladores\\FuncionarioControlador',  'procesarExcel'],
    'GET:/descargar-qrs'     => ['App\\Controladores\\FuncionarioControlador',  'descargarQrsZip'],
    'GET:/api/funcionario'   => ['App\\Controladores\\FuncionarioControlador',  'obtenerDetallesAjax'],
    'GET:/api/funcionarios/obtener'    => ['App\\Controladores\\FuncionarioControlador',  'obtenerAjax'],
    'POST:/api/funcionarios/actualizar' => ['App\\Controladores\\FuncionarioControlador', 'actualizarAjax'],
    'GET:/reportes'          => ['App\\Controladores\\ReporteControlador',      'index'],
    'GET:/reportes/pdf'      => ['App\\Controladores\\ReporteControlador',      'generarHojaQRPdf'],
    'GET:/api/reportes/filtrar' => ['App\\Controladores\\ReporteControlador',   'filtrarAjax'],
    'GET:/respaldos'         => ['App\\Controladores\\RespaldoControlador',     'index'],
    'POST:/respaldos/restaurar' => ['App\\Controladores\\RespaldoControlador',  'restaurarAjax'],
    'POST:/api/respaldos/restaurar' => ['App\\Controladores\\RespaldoControlador', 'restaurarAjax'],
    'GET:/respaldos/descargar' => ['App\\Controladores\\BackupControlador',     'respaldar'],
    'GET:/respaldo-bd'       => ['App\\Controladores\\BackupControlador',       'respaldar'],
    'GET:/usuarios'          => ['App\\Controladores\\UsuarioControlador',      'index'],
    'GET:/perfil'            => ['App\\Controladores\\PerfilControlador',       'index'],
    'POST:/perfil/actualizar-password' => ['App\\Controladores\\PerfilControlador', 'actualizarPassword'],
    'POST:/api/perfil/actualizar-password' => ['App\\Controladores\\PerfilControlador', 'actualizarPassword'],
    'GET:/api/usuarios/obtener'        => ['App\\Controladores\\UsuarioControlador',      'obtenerAjax'],
    'POST:/api/usuarios/actualizar'    => ['App\\Controladores\\UsuarioControlador',      'actualizarAjax'],
    'POST:/usuarios'         => ['App\\Controladores\\UsuarioControlador',      'guardar'],
    'POST:/usuarios/guardar' => ['App\\Controladores\\UsuarioControlador',      'guardar'],
    'POST:/api/usuarios/guardar' => ['App\\Controladores\\UsuarioControlador',  'guardar'],
    'POST:/usuarios/crear'   => ['App\\Controladores\\UsuarioControlador',      'guardar'],
    'GET:/validar'           => ['App\\Controladores\\ValidacionControlador',   'validar'],
    'GET:/certificado'       => ['App\\Controladores\\CertificadoControlador',  'generar'],
    'GET:/404'               => ['App\\Controladores\\ErrorControlador',        'notFound'],
];

$claveRuta = "{$metodo}:{$rutaLimpia}";

// ── 7.1 Guardia Global de Seguridad RBAC (Solo Administrador) ────────────────
// Protege cualquier ruta de exportación, descargas masivas, respaldos o gestión de usuarios
$rutasSoloAdmin = [
    '/respaldos',
    '/respaldos/descargar',
    '/respaldo-bd',
    '/descargar-qrs',
    '/usuarios',
    '/usuarios/guardar',
    '/api/usuarios/guardar',
    '/usuarios/crear',
    '/api/usuarios/obtener',
    '/api/usuarios/actualizar'
];

$esRutaRestringida = in_array($rutaLimpia, $rutasSoloAdmin, true) 
    || str_contains($rutaLimpia, 'respaldo') 
    || str_contains($rutaLimpia, 'exportar');

if ($esRutaRestringida) {
    $rolSesion = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
    $esAdmin   = in_array($rolSesion, ['admin', 'administrador'], true);

    if (!$esAdmin) {
        http_response_code(403);

        // Si la solicitud es asíncrona (Fetch / AJAX), devolver JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Acceso denegado: Se requieren permisos de administrador institucional para esta acción.',
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        // Si es navegación directa, redirigir al dashboard con sesión activa o al login
        $destino = (!empty($_SESSION['auth'])) ? ($baseDir . '/dashboard') : ($baseDir . '/login');
        header('Location: ' . $destino);
        exit();
    }
}

// ── 8. Despacho de la ruta ───────────────────────────────────────────────────
if (array_key_exists($claveRuta, $rutas)) {
    [$clase, $accion] = $rutas[$claveRuta];

    // Verificar que el archivo del controlador exista antes de instanciar
    $archivoControlador = APP_DIR . '/Controladores/' . basename(str_replace('\\', '/', $clase)) . '.php';

    if (file_exists($archivoControlador)) {
        require_once $archivoControlador;
    }

    if (class_exists($clase)) {
        $controlador = new $clase();

        if (method_exists($controlador, $accion)) {
            $controlador->$accion();
        } else {
            // El método no existe en el controlador → 500 interno
            http_response_code(500);
            error_log("[ISPEB Router] Acción '{$accion}' no encontrada en '{$clase}'.");
            die('[ISPEB] Error interno del servidor: acción no encontrada.');
        }
    } else {
        // Clase no existe → intentar cargar el controlador de error
        despacharError404($rutas, APP_DIR);
    }
} else {
    // Ruta no registrada → 404
    despacharError404($rutas, APP_DIR);
}

// ── Función auxiliar: despachar error 404 ────────────────────────────────────
function despacharError404(array $rutas, string $appDir): void
{
    http_response_code(404);

    if (isset($rutas['GET:/404'])) {
        [$clase, $accion] = $rutas['GET:/404'];
        $archivoControlador = $appDir . '/Controladores/' . basename(str_replace('\\', '/', $clase)) . '.php';

        if (file_exists($archivoControlador)) {
            require_once $archivoControlador;
        }

        if (class_exists($clase) && method_exists($clase, $accion)) {
            $controlador = new $clase();
            $controlador->$accion();
            return;
        }
    }

    // Fallback si no existe el controlador de errores
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 – Página no encontrada | ISPEB</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #e2e8f0;
               display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .container { text-align: center; }
        h1 { font-size: 6rem; margin: 0; color: #6366f1; }
        p  { font-size: 1.2rem; color: #94a3b8; }
        a  { color: #6366f1; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p>La ruta solicitada no existe en el sistema institucional.</p>
        <a href="/docencia_qr/login">← Volver al inicio</a>
    </div>
</body>
</html>';
}
