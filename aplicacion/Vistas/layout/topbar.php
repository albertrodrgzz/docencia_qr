<?php
/**
 * Componente Modular: Topbar (Barra Superior Sticky Estilo macOS) — ISPEB
 *
 * @var string|null $tituloVista       Título de la página
 * @var bool|null   $mostrarBotonAtras Define si se muestra el botón circular de regresar
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) ? '/docencia_qr' : '');
}

// Capturar la ruta actual limpia restando BASE_URL para determinar vista raíz
$rutaActual = str_replace(BASE_URL, '', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$rutaLimpia = trim($rutaActual, '/');
if ($rutaLimpia === '') {
    $rutaLimpia = 'dashboard';
}

$esDashboard = in_array($rutaLimpia, ['dashboard', ''], true) || (isset($tituloVista) && in_array(strtolower((string)$tituloVista), ['panel de control', 'dashboard'], true) && ($mostrarBotonAtras ?? false) === false);

$urlAnterior = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/dashboard';
$urlActual = $_SERVER['REQUEST_URI'];

// Si la URL actual ya tiene el flag de retorno, o si no hay referer, forzamos ir al dashboard
if (isset($_GET['volver']) || empty($_SERVER['HTTP_REFERER']) || strpos($urlAnterior, $urlActual) !== false) {
    $urlDestino = BASE_URL . '/dashboard';
} else {
    // Le agregamos la bandera de retroceso a la URL anterior
    $separador = (parse_url($urlAnterior, PHP_URL_QUERY) == NULL) ? '?' : '&';
    $urlDestino = $urlAnterior . $separador . 'volver=true';
}

$nombreSesion  = $_SESSION['nombre'] ?? (!empty($_SESSION['correo']) ? explode('@', $_SESSION['correo'])[0] : 'admin');
$nombreUsuario = htmlspecialchars((string) $nombreSesion, ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars((string) ($_SESSION['rol'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
$badgeIniciales = strtoupper(substr($nombreUsuario, 0, 2)) ?: 'AD';
?>
<header class="topbar" role="banner">
    <!-- Lado Izquierdo: Botón Hamburguesa Off-canvas y Botón de retroceso -->
    <div class="topbar-left">
        <!-- Botón de apertura de menú en pantallas móviles/tablets -->
        <button type="button" class="btn-toggle-sidebar" id="btnToggleSidebar" aria-label="Abrir menú de navegación" title="Menú">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <?php if (!$esDashboard): ?>
            <a href="<?= htmlspecialchars($urlDestino, ENT_QUOTES, 'UTF-8') ?>" class="topbar-btn-atras" title="Regresar" aria-label="Atrás">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span>Atrás</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Lado Derecho: Píldora redondeada de usuario con avatar circular, nombre, rol y punto verde de estado -->
    <div class="topbar-right">
        <!-- Píldora de Perfil de Usuario -->
        <a href="<?= BASE_URL ?>/perfil" class="user-pill-badge" title="Ver mi perfil institucional">
            <div class="user-avatar-circle" aria-hidden="true"><?= $badgeIniciales ?></div>
            <div class="user-pill-text">
                <span class="user-pill-name"><?= $nombreUsuario ?></span>
                <span class="user-pill-role"><?= $rolUsuario ?></span>
            </div>
            <span class="user-live-dot" title="Usuario en línea" aria-label="En línea"></span>
        </a>

        <!-- Botón Cerrar Sesión -->
        <a href="javascript:void(0)" onclick="confirmarSalida(event)" class="btn-topbar-logout" title="Cerrar sesión institucional">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</header>
