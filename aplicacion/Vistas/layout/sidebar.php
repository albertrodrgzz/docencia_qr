<?php
/**
 * Componente Modular: Sidebar (Menú Lateral Fijo) — ISPEB
 * Ecosistema macOS / iPadOS
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) ? '/docencia_qr' : '');
}

// Capturar la ruta actual limpia restando BASE_URL
$rutaActual = str_replace(BASE_URL, '', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$rutaLimpia = trim($rutaActual, '/');
if ($rutaLimpia === '') {
    $rutaLimpia = 'dashboard';
}

$esAdmin = in_array(strtolower((string)($_SESSION['rol'] ?? '')), ['admin', 'administrador'], true);
?>
<aside class="sidebar" role="navigation" aria-label="Menú Principal">
    
    <!-- Logotipo Institucional ISPEB -->
    <a href="<?= BASE_URL ?>/dashboard" class="sidebar-brand">
        <div class="brand-icon-sq" aria-hidden="true">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div class="brand-text">
            <span class="brand-name">ISPEB</span>
            <span class="brand-desc">Certificaciones QR</span>
        </div>
    </a>

    <!-- Menú Vertical de Navegación -->
    <nav class="sidebar-nav">
        <span class="nav-section-title">Principal</span>

        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>/dashboard" 
           class="nav-item <?= in_array($rutaLimpia, ['dashboard', ''], true) ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
            <span>Dashboard</span>
        </a>

        <span class="nav-section-title">Gestión</span>

        <!-- Funcionarios -->
        <a href="<?= BASE_URL ?>/funcionarios" 
           class="nav-item <?= ($rutaLimpia === 'funcionarios') ? 'active' : '' ?>">
            <i class="fa-solid fa-users" aria-hidden="true"></i>
            <span>Funcionarios</span>
        </a>

        <!-- Reportes (Acceso Completo para Todos los Usuarios) -->
        <a href="<?= BASE_URL ?>/reportes" 
           class="nav-item <?= in_array($rutaLimpia, ['reportes', 'reportes/pdf'], true) ? 'active' : '' ?>">
            <i class="fa-solid fa-file-shield" aria-hidden="true"></i>
            <span>Reportes</span>
        </a>

        <!-- Restringido solo para Administradores -->
        <?php if ($esAdmin): ?>
            <span class="nav-section-title">Administración</span>

            <!-- Usuarios (Acceso Administrador) -->
            <a href="<?= BASE_URL ?>/usuarios" 
               class="nav-item <?= str_starts_with($rutaLimpia, 'usuarios') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                <span>Usuarios</span>
            </a>

            <!-- Respaldos (Acceso Administrador) -->
            <a href="<?= BASE_URL ?>/respaldos" 
               class="nav-item <?= (in_array($rutaLimpia, ['respaldos', 'respaldo-bd'], true) || str_starts_with($rutaLimpia, 'respaldo')) ? 'active' : '' ?>">
                <i class="fa-solid fa-database" aria-hidden="true"></i>
                <span>Respaldos</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Estado Inferior Flotante Neo-Corporate -->
    <div class="sidebar-bottom-status" title="Sistema en línea y seguro">
        <span class="status-dot-green" aria-hidden="true"></span>
        <span class="status-text-shell">En línea v2.4.0 • Vision Shell</span>
    </div>
</aside>
