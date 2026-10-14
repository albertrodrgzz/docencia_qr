<?php
/**
 * Vista de Gestión de Usuarios — ISPEB Sistema Institucional de Certificaciones
 * Control de Acceso Basado en Roles (RBAC) · Estética macOS / iOS Glassmorphism
 *
 * @var array<int, array<string, mixed>> $usuarios Lista provista por UsuarioControlador::index()
 */

$baseUrl = (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) 
    ? '/docencia_qr' 
    : '';

$correoUsuario = htmlspecialchars((string) ($_SESSION['correo'] ?? 'admin@ispeb.gob.ve'), ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars((string) ($_SESSION['rol'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
$listaUsuarios = (array) ($usuarios ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Gestión y Control de Usuarios del Sistema ISPEB">
    <title>Gestión de Usuarios — ISPEB</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Tokens de Diseño Apple macOS / iOS ── */
        :root {
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-blue-subtle: rgba(0, 113, 227, 0.08);
            --apple-purple: #af52de;
            --apple-purple-subtle: rgba(175, 82, 222, 0.12);
            --apple-green: #34c759;
            --apple-green-subtle: rgba(52, 199, 89, 0.12);
            --apple-red: #ff3b30;
            --apple-red-subtle: rgba(255, 59, 48, 0.10);
            --bg-base: #f5f5f7;
            --surface-card: rgba(255, 255, 255, 0.82);
            --border-glass: rgba(255, 255, 255, 0.85);
            --border-light: rgba(0, 0, 0, 0.06);
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #a1a1a6;
            --font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Helvetica, Arial, sans-serif;
            --radius-card: 22px;
            --radius-btn: 12px;
            --shadow-subtle: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            --shadow-elevated: 0 16px 36px -8px rgba(0, 0, 0, 0.06), 0 0 1px 1px rgba(255, 255, 255, 0.7) inset;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
        }

        /* ── Fondo dinámico ── */
        .ambient-bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background: 
                radial-gradient(circle at 12% 16%, rgba(206, 226, 255, 0.45) 0%, transparent 40%),
                radial-gradient(circle at 88% 12%, rgba(228, 219, 255, 0.40) 0%, transparent 40%),
                radial-gradient(circle at 50% 90%, rgba(210, 238, 255, 0.40) 0%, transparent 50%),
                #f5f5f7;
        }

        /* ── Navbar macOS Simplificado ── */
        .macos-navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            height: 64px;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(245, 245, 247, 0.75);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-bottom: 1px solid var(--border-light);
        }

        .nav-left-group {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-badge-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 119, 237, 0.25);
        }

        .brand-badge-icon svg {
            width: 19px;
            height: 19px;
            fill: currentColor;
        }

        .brand-meta-title {
            font-size: 0.96rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .nav-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--apple-blue);
            background: var(--apple-blue-subtle);
            text-decoration: none;
            transition: all 0.16s ease;
        }

        .nav-back-link:hover {
            background: rgba(0, 113, 227, 0.16);
            transform: translateX(-2px);
        }

        .nav-back-link svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2.2;
            fill: none;
        }

        .nav-user-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-role-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            background: rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            font-size: 0.81rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .role-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--apple-purple);
            box-shadow: 0 0 6px rgba(175, 82, 222, 0.6);
        }

        .user-role-pill strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--apple-red);
            background: var(--apple-red-subtle);
            text-decoration: none;
            transition: all 0.18s ease;
        }

        .btn-logout:hover {
            background: var(--apple-red);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-logout svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
            stroke: currentColor;
            fill: none;
        }

        /* ── Contenedor Principal ── */
        .main-layout {
            position: relative;
            z-index: 1;
            max-width: 1160px;
            width: 100%;
            margin: 0 auto;
            padding: 34px 28px 60px;
            flex: 1;
        }

        /* ── Header de Sección ── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .section-title-group h2 {
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--text-primary);
        }

        .section-title-group p {
            font-size: 0.86rem;
            color: var(--text-secondary);
            margin-top: 3px;
        }

        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 18px;
            border-radius: var(--radius-btn);
            font-family: inherit;
            font-size: 0.86rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            cursor: pointer;
            background: var(--apple-blue);
            color: #ffffff;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-action-primary:hover {
            background: var(--apple-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 113, 227, 0.32);
        }

        .btn-action-primary svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        /* ── Tarjeta de la Tabla macOS ── */
        .table-card {
            background: var(--surface-card);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-elevated);
            overflow: hidden;
        }

        .table-card-header {
            padding: 22px 26px 18px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .table-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }

        .table-subtitle {
            font-size: 0.78rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .badge-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 10px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 20px;
            color: var(--text-secondary);
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .macos-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
            text-align: left;
        }

        .macos-table th {
            padding: 14px 26px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            background: rgba(0, 0, 0, 0.015);
            border-bottom: 1px solid var(--border-light);
            white-space: nowrap;
        }

        .macos-table td {
            padding: 16px 26px;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            vertical-align: middle;
            transition: background 0.15s ease;
        }

        .macos-table tbody tr:last-child td {
            border-bottom: none;
        }

        .macos-table tbody tr:hover td {
            background: rgba(0, 113, 227, 0.025);
        }

        .user-avatar-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
            color: white;
            font-size: 0.74rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            vertical-align: middle;
        }

        .user-name-text {
            font-weight: 600;
            color: var(--text-primary);
        }

        .email-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.81rem;
            color: var(--text-secondary);
        }

        /* Chips de Roles (RBAC) */
        .role-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .role-chip.admin {
            background: var(--apple-purple-subtle);
            color: var(--apple-purple);
        }

        .role-chip.estandar {
            background: var(--apple-blue-subtle);
            color: var(--apple-blue);
        }

        .role-dot-small {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 12px;
            background: var(--apple-green-subtle);
            color: #248a3d;
        }

        /* ── Modal Estilo iOS / macOS ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 999;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.16);
            transform: scale(0.96) translateY(12px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .modal-overlay.active .modal-box {
            transform: scale(1) translateY(0);
        }

        .modal-head {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-head-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }

        .modal-close-btn {
            background: rgba(0, 0, 0, 0.05);
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.15s ease;
        }

        .modal-close-btn:hover {
            background: rgba(255, 59, 48, 0.12);
            color: var(--apple-red);
        }

        .modal-close-btn svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2;
        }

        .modal-body {
            padding: 22px 24px;
        }

        .modal-form-group {
            margin-bottom: 15px;
        }

        .modal-label {
            display: block;
            font-size: 0.77rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            letter-spacing: -0.01em;
        }

        .modal-input {
            width: 100%;
            height: 42px;
            padding: 0 14px;
            font-family: inherit;
            font-size: 0.90rem;
            color: var(--text-primary);
            background: rgba(0, 0, 0, 0.035);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 11px;
            outline: none;
            transition: all 0.2s ease;
        }

        .modal-input:focus {
            background: #ffffff;
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.16);
        }

        /* Selector de Rol Estilo Segmented iOS */
        .role-selector-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 4px;
        }

        .role-option-card {
            border: 1.5px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.18s ease;
            background: rgba(0, 0, 0, 0.02);
            position: relative;
        }

        .role-option-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .role-option-card:hover {
            background: rgba(0, 113, 227, 0.03);
            border-color: rgba(0, 113, 227, 0.3);
        }

        .role-option-card.selected {
            background: rgba(0, 113, 227, 0.06);
            border-color: var(--apple-blue);
        }

        .role-card-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            display: block;
        }

        .role-card-desc {
            font-size: 0.72rem;
            color: var(--text-secondary);
            margin-top: 2px;
            display: block;
            line-height: 1.25;
        }

        .modal-foot {
            padding: 16px 24px 20px;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-modal-cancel {
            height: 38px;
            padding: 0 16px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 500;
            background: transparent;
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--text-secondary);
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .btn-modal-cancel:hover {
            background: rgba(0, 0, 0, 0.04);
            color: var(--text-primary);
        }

        .btn-modal-submit {
            height: 38px;
            padding: 0 18px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            background: var(--apple-blue);
            border: none;
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.2);
            transition: all 0.15s ease;
        }

        .btn-modal-submit:hover {
            background: var(--apple-blue-hover);
        }

        .btn-modal-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ── Estilos SweetAlert2 ── */
        .swal2-popup.apple-modal {
            border-radius: 20px !important;
            padding: 24px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(25px) !important;
            font-family: var(--font-family) !important;
        }

        .swal2-confirm.apple-btn {
            background-color: var(--apple-blue) !important;
            border-radius: 10px !important;
            padding: 9px 22px !important;
            font-size: 0.86rem !important;
            font-weight: 600 !important;
        }

        @media (max-width: 768px) {
            .macos-navbar { padding: 0 20px; }
            .main-layout { padding: 24px 16px 40px; }
            .role-selector-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Fondo ambiental -->
    <div class="ambient-bg" aria-hidden="true"></div>

    <!-- ── Navbar macOS Simplificado ── -->
    <header class="macos-navbar">
        <div class="nav-left-group">
            <a href="<?= htmlspecialchars($baseUrl) ?>/dashboard" class="nav-brand">
                <div class="brand-badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 16l-4-4 1.41-1.41L11 14.17l6.59-6.59L19 9l-8 8z"/></svg>
                </div>
                <span class="brand-meta-title">ISPEB</span>
            </a>

            <!-- Botón Volver al Dashboard -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/dashboard" class="nav-back-link">
                <svg viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-user-actions">
            <!-- Rol actual -->
            <div class="user-role-pill">
                <span class="role-dot" aria-hidden="true"></span>
                <span>Rol: <strong><?= $rolUsuario ?></strong></span>
            </div>

            <!-- Botón Cerrar Sesión -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/logout" class="btn-logout" title="Cerrar Sesión">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Salir</span>
            </a>
        </div>
    </header>

    <!-- ── Layout Principal ── -->
    <main class="main-layout">

        <!-- Encabezado de la Sección -->
        <section class="section-header">
            <div class="section-title-group">
                <h2>Gestión de Usuarios</h2>
                <p>Administración de credenciales y asignación de roles con restricción RBAC.</p>
            </div>

            <button type="button" class="btn-action-primary" id="btnAbrirModalUsuario">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Nuevo Usuario</span>
            </button>
        </section>

        <!-- ── Tabla de Usuarios Estilo macOS ── -->
        <section class="table-card">
            <div class="table-card-header">
                <div>
                    <h3 class="table-title">Usuarios Registrados</h3>
                    <p class="table-subtitle">Operadores con acceso al panel administrativo</p>
                </div>
                <span class="badge-count"><?= count($listaUsuarios) ?> usuarios</span>
            </div>

            <div class="table-responsive">
                <table class="macos-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Correo Electrónico</th>
                            <th>Rol (RBAC)</th>
                            <th>Estado</th>
                            <th>Fecha Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listaUsuarios as $usr): 
                            $rolVal = strtolower((string)($usr['rol'] ?? 'estandar'));
                            $esRolAdmin = in_array($rolVal, ['admin', 'administrador'], true);
                            $nombreMostrar = trim((string)($usr['nombre'] ?? '')) ?: 'Usuario Institucional';
                            $iniciales = strtoupper(substr($nombreMostrar, 0, 2));
                        ?>
                            <tr>
                                <td>
                                    <span class="user-avatar-badge"><?= $iniciales ?></span>
                                    <span class="user-name-text"><?= htmlspecialchars($nombreMostrar) ?></span>
                                </td>
                                <td>
                                    <span class="email-code"><?= htmlspecialchars((string)($usr['correo'] ?? '—')) ?></span>
                                </td>
                                <td>
                                    <span class="role-chip <?= $esRolAdmin ? 'admin' : 'estandar' ?>">
                                        <span class="role-dot-small"></span>
                                        <?= $esRolAdmin ? 'Administrador' : 'Estándar' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-chip">
                                        <?= htmlspecialchars((string)($usr['estado'] ?? 'Activo')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($usr['fecha_registro']) ? date('d/m/Y h:i A', strtotime((string)$usr['fecha_registro'])) : '—' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <!-- ── Modal Estilo iOS: Crear Nuevo Usuario ── -->
    <div class="modal-overlay" id="modalUsuario" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-head-title">Crear Nuevo Usuario</h3>
                <button type="button" class="modal-close-btn" id="btnCerrarModal" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="formCrearUsuario" autocomplete="off">
                <div class="modal-body">
                    
                    <div class="modal-form-group">
                        <label class="modal-label" for="usr_nombre">Nombre Completo</label>
                        <input type="text" id="usr_nombre" name="nombre" class="modal-input" placeholder="Ej: Inspector Roberto Martínez" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="usr_correo">Correo Institucional</label>
                        <input type="email" id="usr_correo" name="correo" class="modal-input" placeholder="usuario@ispeb.gob.ve" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label" for="usr_password">Contraseña de Acceso</label>
                        <input type="password" id="usr_password" name="password" class="modal-input" placeholder="Mínimo 6 caracteres" minlength="6" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-label">Rol del Usuario (RBAC)</label>
                        <div class="role-selector-grid">
                            
                            <!-- Opción: Estándar -->
                            <label class="role-option-card selected" id="optEstandar">
                                <input type="radio" name="rol" value="estandar" checked>
                                <span class="role-card-title">Estándar</span>
                                <span class="role-card-desc">Emisión y registro de funcionarios con código QR.</span>
                            </label>

                            <!-- Opción: Administrador -->
                            <label class="role-option-card" id="optAdmin">
                                <input type="radio" name="rol" value="admin">
                                <span class="role-card-title">Administrador</span>
                                <span class="role-card-desc">Control total del sistema, gestión de usuarios y respaldo BD.</span>
                            </label>

                        </div>
                    </div>

                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-modal-cancel" id="btnCancelarModal">Cancelar</button>
                    <button type="submit" class="btn-modal-submit" id="btnSubmitUsuario">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Lógica Frontend ── -->
    <script>
    (function () {
        'use strict';

        const BASE_URL         = '<?= $baseUrl ?>';
        const modalUsuario     = document.getElementById('modalUsuario');
        const btnAbrirModal    = document.getElementById('btnAbrirModalUsuario');
        const btnCerrarModal   = document.getElementById('btnCerrarModal');
        const btnCancelarModal = document.getElementById('btnCancelarModal');
        const formUsuario      = document.getElementById('formCrearUsuario');
        const btnSubmitUsuario = document.getElementById('btnSubmitUsuario');

        const optEstandar = document.getElementById('optEstandar');
        const optAdmin    = document.getElementById('optAdmin');

        // Control visual de la selección de rol
        [optEstandar, optAdmin].forEach(card => {
            card?.addEventListener('click', () => {
                optEstandar.classList.remove('selected');
                optAdmin.classList.remove('selected');
                card.classList.add('selected');
                card.querySelector('input[type="radio"]').checked = true;
            });
        });

        function toggleModal(show) {
            modalUsuario.classList.toggle('active', show);
            modalUsuario.setAttribute('aria-hidden', !show);
            document.body.style.overflow = show ? 'hidden' : '';
        }

        btnAbrirModal?.addEventListener('click', () => {
            formUsuario?.reset();
            optEstandar.classList.add('selected');
            optAdmin.classList.remove('selected');
            optEstandar.querySelector('input').checked = true;
            toggleModal(true);
            document.getElementById('usr_nombre')?.focus();
        });

        btnCerrarModal?.addEventListener('click', () => toggleModal(false));
        btnCancelarModal?.addEventListener('click', () => toggleModal(false));

        modalUsuario?.addEventListener('click', (e) => {
            if (e.target === modalUsuario) toggleModal(false);
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalUsuario.classList.contains('active')) {
                toggleModal(false);
            }
        });

        // Envío asíncrono del formulario de usuario
        formUsuario?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const nombre   = document.getElementById('usr_nombre').value.trim();
            const correo   = document.getElementById('usr_correo').value.trim();
            const password = document.getElementById('usr_password').value;
            const rolInput = formUsuario.querySelector('input[name="rol"]:checked');
            const rol      = rolInput ? rolInput.value : 'estandar';

            if (!nombre || !correo || !password) {
                Swal.fire({
                    title: 'Campos requeridos',
                    text: 'Completa todos los campos obligatorios.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
                return;
            }

            btnSubmitUsuario.disabled = true;
            btnSubmitUsuario.textContent = 'Creando…';

            try {
                const formData = new FormData();
                formData.append('nombre', nombre);
                formData.append('correo', correo);
                formData.append('password', password);
                formData.append('rol', rol);

                const response = await fetch(BASE_URL + '/usuarios', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (data.exito) {
                    toggleModal(false);
                    await Swal.fire({
                        title: '¡Usuario Creado!',
                        text: data.mensaje || 'El usuario ha sido registrado exitosamente.',
                        icon: 'success',
                        confirmButtonText: 'Continuar',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: 'Error al registrar',
                        text: data.mensaje || 'No se pudo crear el usuario.',
                        icon: 'error',
                        confirmButtonText: 'Revisar',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    title: 'Error de Red',
                    text: 'No fue posible comunicar con el servidor.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
            } finally {
                btnSubmitUsuario.disabled = false;
                btnSubmitUsuario.textContent = 'Crear Usuario';
            }
        });

    })();
    </script>
</body>
</html>
