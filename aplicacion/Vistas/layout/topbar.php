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

$nombreSesion  = $_SESSION['nombre'] ?? (!empty($_SESSION['correo']) ? explode('@', $_SESSION['correo'])[0] : 'admin');
$nombreUsuario = htmlspecialchars((string) $nombreSesion, ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars((string) ($_SESSION['rol'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
$badgeIniciales = strtoupper(substr($nombreUsuario, 0, 2)) ?: 'AD';
?>
<header class="topbar">
    <!-- Lado Izquierdo: Título de la página y botón atrás si aplica -->
    <div class="topbar-left">
        <?php if (!empty($mostrarBotonAtras)): ?>
            <button type="button" class="btn-back" onclick="history.back()" title="Regresar a la página anterior" aria-label="Volver">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            </button>
        <?php endif; ?>
        <h1 class="page-title"><?= htmlspecialchars($tituloVista ?? 'Panel', ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <!-- Lado Derecho: Tarjeta de Usuario con Badge y Botón Cerrar Sesión -->
    <div class="topbar-right">
        <!-- Enlace al Perfil del Usuario -->
        <a href="<?= BASE_URL ?>/perfil" class="user-profile-badge" title="Ver mi perfil institucional">
            <div class="user-avatar-initials"><?= $badgeIniciales ?></div>
            <div class="user-text-info">
                <span class="user-text-name"><?= $nombreUsuario ?></span>
                <span class="user-text-role"><?= $rolUsuario ?></span>
            </div>
        </a>

        <a href="javascript:void(0)" onclick="confirmarSalida(event)" class="btn-topbar-logout" title="Cerrar sesión institucional">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</header>
