<?php
/**
 * Vista del Panel de Control (Dashboard) — ISPEB Sistema Institucional de Certificaciones
 * Estética Hiper-minimalista macOS / iOS (Glassmorphism, Apple HIG, Datos Reales)
 *
 * @var array{
 *     total_funcionarios: int,
 *     registrados_hoy: int,
 *     ultimos_funcionarios: array<int, array<string, mixed>>
 * } $datos Estadísticas provistas por DashboardControlador
 */

$baseUrl = (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) 
    ? '/docencia_qr' 
    : '';

$correoUsuario = htmlspecialchars((string) ($_SESSION['correo'] ?? 'usuario@ispeb.gob.ve'), ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars((string) ($_SESSION['rol'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
$esAdmin       = in_array(strtolower((string)($_SESSION['rol'] ?? '')), ['admin', 'administrador'], true);

$totalFuncionarios = (int) ($datos['total_funcionarios'] ?? 0);
$registradosHoy    = (int) ($datos['registrados_hoy'] ?? 0);
$ultimosRegistros  = (array) ($datos['ultimos_funcionarios'] ?? []);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard Institucional — Sistema de Certificaciones ISPEB">
    <title>Panel de Control — ISPEB</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Variables de Diseño Apple macOS / iOS ── */
        :root {
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-blue-subtle: rgba(0, 113, 227, 0.08);
            --apple-green: #34c759;
            --apple-green-subtle: rgba(52, 199, 89, 0.12);
            --apple-purple: #af52de;
            --apple-purple-subtle: rgba(175, 82, 222, 0.10);
            --apple-red: #ff3b30;
            --apple-red-subtle: rgba(255, 59, 48, 0.10);
            --bg-base: #f5f5f7;
            --surface-card: rgba(255, 255, 255, 0.82);
            --surface-glass: rgba(255, 255, 255, 0.65);
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

        /* ── Lienzo ambiental de fondo ── */
        .ambient-bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background: 
                radial-gradient(circle at 10% 15%, rgba(206, 226, 255, 0.45) 0%, transparent 40%),
                radial-gradient(circle at 90% 10%, rgba(228, 219, 255, 0.40) 0%, transparent 40%),
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

        .brand-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .brand-meta-title {
            font-size: 0.96rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-meta-sub {
            font-size: 0.74rem;
            color: var(--text-secondary);
        }

        .nav-user-actions {
            display: flex;
            align-items: center;
            gap: 16px;
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
            background: var(--apple-green);
            box-shadow: 0 0 6px rgba(52, 199, 89, 0.6);
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
            box-shadow: 0 4px 12px rgba(255, 59, 48, 0.25);
        }

        .btn-logout svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
            stroke: currentColor;
            fill: none;
        }

        /* ── Contenido Principal ── */
        .main-layout {
            position: relative;
            z-index: 1;
            max-width: 1160px;
            width: 100%;
            margin: 0 auto;
            padding: 34px 28px 60px;
            flex: 1;
        }

        /* ── Encabezado de la Sección y Acciones ── */
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dashboard-title-group h2 {
            font-size: 1.55rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--text-primary);
        }

        .dashboard-title-group p {
            font-size: 0.86rem;
            color: var(--text-secondary);
            margin-top: 3px;
        }

        .actions-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 40px;
            padding: 0 16px;
            border-radius: var(--radius-btn);
            font-family: inherit;
            font-size: 0.86rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-action svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .btn-action-primary {
            background: var(--apple-blue);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
        }

        .btn-action-primary:hover {
            background: var(--apple-blue-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 113, 227, 0.32);
        }

        .btn-action-secondary {
            background: var(--surface-card);
            color: var(--apple-blue);
            border: 1px solid rgba(0, 113, 227, 0.2);
            box-shadow: var(--shadow-subtle);
        }

        .btn-action-secondary:hover {
            background: #ffffff;
            border-color: var(--apple-blue);
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.15);
        }

        .btn-action-admin {
            background: rgba(175, 82, 222, 0.09);
            color: var(--apple-purple);
            border: 1px solid rgba(175, 82, 222, 0.25);
        }

        .btn-action-admin:hover {
            background: var(--apple-purple);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(175, 82, 222, 0.25);
        }

        .btn-action:active {
            transform: scale(0.98);
        }

        /* ── Grid de Métricas (Exactamente 3 Widgets) ── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: var(--surface-card);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-card);
            padding: 24px 22px;
            box-shadow: var(--shadow-elevated);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 42px -10px rgba(0, 0, 0, 0.08);
        }

        .metric-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .metric-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .metric-icon-wrap.blue {
            background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
            box-shadow: 0 4px 12px rgba(0, 119, 237, 0.3);
        }

        .metric-icon-wrap.green {
            background: linear-gradient(135deg, #34c759 0%, #248a3d 100%);
            box-shadow: 0 4px 12px rgba(52, 199, 89, 0.3);
        }

        .metric-icon-wrap.purple {
            background: linear-gradient(135deg, #af52de 0%, #7e2db7 100%);
            box-shadow: 0 4px 12px rgba(175, 82, 222, 0.3);
        }

        .metric-icon-wrap svg {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .metric-pill-tag {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 9px;
            border-radius: 12px;
            letter-spacing: -0.01em;
        }

        .tag-blue { background: var(--apple-blue-subtle); color: var(--apple-blue); }
        .tag-green { background: var(--apple-green-subtle); color: #248a3d; }
        .tag-purple { background: var(--apple-purple-subtle); color: var(--apple-purple); }

        .metric-number {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.035em;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .metric-label {
            font-size: 0.88rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        /* ── Tabla Estilo macOS (Últimos 5 Registros) ── */
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

        .cedula-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.81rem;
            font-weight: 600;
            padding: 4px 8px;
            background: rgba(0, 0, 0, 0.04);
            border-radius: 6px;
            color: var(--text-primary);
        }

        .funcionario-nombre {
            font-weight: 600;
            color: var(--text-primary);
            display: block;
        }

        .funcionario-cargo {
            font-size: 0.76rem;
            color: var(--text-secondary);
            margin-top: 1px;
            display: block;
        }

        .departamento-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: var(--apple-green-subtle);
            color: #248a3d;
        }

        .status-chip .chip-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--apple-green);
        }

        /* Estado Vacío de la Tabla */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: var(--text-secondary);
        }

        .empty-state-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-tertiary);
        }

        .empty-state-icon svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
        }

        .empty-state h3 {
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: 0.82rem;
            margin-top: 4px;
        }

        /* ── Modales Glassmorphism macOS ── */
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
            background: rgba(255, 255, 255, 0.94);
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

        /* ── Estilos de SweetAlert2 macOS ── */
        .swal2-popup.apple-modal {
            border-radius: 20px !important;
            padding: 24px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(25px) !important;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.12) !important;
            font-family: var(--font-family) !important;
        }

        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 600 !important;
            color: var(--text-primary) !important;
        }

        .swal2-confirm.apple-btn {
            background-color: var(--apple-blue) !important;
            border-radius: 10px !important;
            padding: 9px 22px !important;
            font-size: 0.86rem !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }

        /* ── Responsivo ── */
        @media (max-width: 900px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            .macos-navbar {
                padding: 0 20px;
            }
            .main-layout {
                padding: 24px 16px 40px;
            }
        }
    </style>
</head>
<body>

    <!-- Fondo ambiental -->
    <div class="ambient-bg" aria-hidden="true"></div>

    <!-- ── Navbar Simplificado macOS ── -->
    <header class="macos-navbar">
        <a href="<?= htmlspecialchars($baseUrl) ?>/dashboard" class="nav-brand">
            <div class="brand-badge-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 16l-4-4 1.41-1.41L11 14.17l6.59-6.59L19 9l-8 8z"/></svg>
            </div>
            <div class="brand-meta">
                <span class="brand-meta-title">ISPEB</span>
                <span class="brand-meta-sub">Certificación QR</span>
            </div>
        </a>

        <div class="nav-user-actions">
            <!-- Rol de usuario conectado -->
            <div class="user-role-pill">
                <span class="role-dot" aria-hidden="true"></span>
                <span>Rol: <strong><?= $rolUsuario ?></strong></span>
            </div>

            <!-- Botón elegante de Cerrar Sesión -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/logout" class="btn-logout" title="Cerrar Sesión Segura">
                <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Salir</span>
            </a>
        </div>
    </header>

    <!-- ── Layout Principal ── -->
    <main class="main-layout">

        <!-- Barra de Título y Acciones estrictamente necesarias -->
        <section class="dashboard-header">
            <div class="dashboard-title-group">
                <h2>Panel Institucional</h2>
                <p>Resumen general de certificaciones y registros de funcionarios.</p>
            </div>

            <div class="actions-toolbar">
                <!-- Acción 1: Nuevo Registro -->
                <button type="button" class="btn-action btn-action-primary" id="btnNuevoRegistro">
                    <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Nuevo Registro</span>
                </button>

                <!-- Acción 2: Carga Excel -->
                <button type="button" class="btn-action btn-action-secondary" id="btnCargaExcel">
                    <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span>Carga Excel</span>
                </button>

                <!-- Acciones Condicionales de Administrador (Solo rol admin) -->
                <?php if ($esAdmin): ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/usuarios" class="btn-action btn-action-admin" id="btnUsuarios" title="Gestión de usuarios y accesos (RBAC)">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>Usuarios</span>
                    </a>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/respaldo-bd" class="btn-action btn-action-admin" id="btnRespaldoBd" title="Generar copia de seguridad de la base de datos">
                        <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        <span>Respaldo BD</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── 3 Widgets de Métricas con Datos Reales ── -->
        <section class="metrics-grid">
            
            <!-- Widget 1: Total Funcionarios -->
            <div class="metric-card">
                <div class="metric-card-header">
                    <div class="metric-icon-wrap blue" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <span class="metric-pill-tag tag-blue">Total Registrados</span>
                </div>
                <div class="metric-number"><?= number_format($totalFuncionarios) ?></div>
                <div class="metric-label">Funcionarios en Sistema</div>
            </div>

            <!-- Widget 2: Registrados Hoy -->
            <div class="metric-card">
                <div class="metric-card-header">
                    <div class="metric-icon-wrap green" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <span class="metric-pill-tag tag-green">Hoy</span>
                </div>
                <div class="metric-number"><?= number_format($registradosHoy) ?></div>
                <div class="metric-label">Registros Efectuados Hoy</div>
            </div>

            <!-- Widget 3: Certificados QR Generados -->
            <div class="metric-card">
                <div class="metric-card-header">
                    <div class="metric-icon-wrap purple" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    </div>
                    <span class="metric-pill-tag tag-purple">Verificados</span>
                </div>
                <div class="metric-number"><?= number_format($totalFuncionarios) ?></div>
                <div class="metric-label">Certificaciones QR Activas</div>
            </div>

        </section>

        <!-- ── Tabla Estilizada macOS: Últimos 5 Registros ── -->
        <section class="table-card">
            <div class="table-card-header">
                <div>
                    <h3 class="table-title">Últimos Funcionarios Registrados</h3>
                    <p class="table-subtitle">Registros recientes integrados al sistema de verificación</p>
                </div>
                <span class="badge-count"><?= count($ultimosRegistros) ?> de 5 recientes</span>
            </div>

            <div class="table-responsive">
                <?php if (empty($ultimosRegistros)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <h3>No hay funcionarios registrados todavía</h3>
                        <p>Utiliza el botón "Nuevo Registro" o "Carga Excel" para comenzar.</p>
                    </div>
                <?php else: ?>
                    <table class="macos-table">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Funcionario / Cargo</th>
                                <th>Departamento</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosRegistros as $func): ?>
                                <tr>
                                    <td>
                                        <span class="cedula-badge"><?= htmlspecialchars((string)($func['cedula'] ?? '—')) ?></span>
                                    </td>
                                    <td>
                                        <span class="funcionario-nombre"><?= htmlspecialchars((string)($func['nombre_completo'] ?? '—')) ?></span>
                                        <span class="funcionario-cargo"><?= htmlspecialchars((string)($func['cargo'] ?? '—')) ?></span>
                                    </td>
                                    <td>
                                        <span class="departamento-tag"><?= htmlspecialchars((string)($func['departamento'] ?? '—')) ?></span>
                                    </td>
                                    <td>
                                        <?= !empty($func['fecha_registro']) ? date('d/m/Y h:i A', strtotime((string)$func['fecha_registro'])) : 'Reciente' ?>
                                    </td>
                                    <td>
                                        <span class="status-chip">
                                            <span class="chip-dot"></span>
                                            QR Emitido
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- ── Modal: Nuevo Registro Manual ── -->
    <div class="modal-overlay" id="modalRegistro" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-head-title">Nuevo Registro de Funcionario</h3>
                <button type="button" class="modal-close-btn" id="btnCerrarRegistro" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="formRegistroManual" autocomplete="off">
                <div class="modal-body">
                    <div class="modal-form-group">
                        <label class="modal-label" for="reg_cedula">Cédula de Identidad</label>
                        <input type="text" id="reg_cedula" name="cedula" class="modal-input" placeholder="Ej: V-12345678" required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="reg_nombre">Nombre Completo</label>
                        <input type="text" id="reg_nombre" name="nombre" class="modal-input" placeholder="Ej: Carlos Silva" required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="reg_cargo">Cargo Institucional</label>
                        <input type="text" id="reg_cargo" name="cargo" class="modal-input" placeholder="Ej: Oficial Docente" required>
                    </div>
                    <div class="modal-form-group">
                        <label class="modal-label" for="reg_departamento">Departamento / Adscripción</label>
                        <input type="text" id="reg_departamento" name="departamento" class="modal-input" placeholder="Ej: Dirección Académica" required>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-modal-cancel" id="btnCancelarRegistro">Cancelar</button>
                    <button type="submit" class="btn-modal-submit" id="btnGuardarRegistro">Guardar y Generar QR</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Modal: Carga Masiva Excel ── -->
    <div class="modal-overlay" id="modalExcel" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-box">
            <div class="modal-head">
                <h3 class="modal-head-title">Carga Masiva desde Excel</h3>
                <button type="button" class="modal-close-btn" id="btnCerrarExcel" aria-label="Cerrar">
                    <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <form id="formCargaExcel" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-body">
                    <p style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 14px; line-height: 1.4;">
                        El archivo <strong>.xlsx</strong> debe tener en la fila 1 los encabezados: <em>Cédula, Nombre, Cargo, Departamento</em>.
                    </p>
                    <div class="modal-form-group">
                        <label class="modal-label" for="documento_excel">Seleccionar Archivo Excel (.xlsx)</label>
                        <input type="file" id="documento_excel" name="documento_excel" class="modal-input" accept=".xlsx" required style="padding-top: 8px;">
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-modal-cancel" id="btnCancelarExcel">Cancelar</button>
                    <button type="submit" class="btn-modal-submit" id="btnProcesarExcel">Importar y Procesar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Script de Modales y Fetch API ── -->
    <script>
    (function () {
        'use strict';

        const BASE_URL = '<?= $baseUrl ?>';

        // Elementos Modal Registro
        const modalRegistro = document.getElementById('modalRegistro');
        const btnNuevoRegistro = document.getElementById('btnNuevoRegistro');
        const btnCerrarRegistro = document.getElementById('btnCerrarRegistro');
        const btnCancelarRegistro = document.getElementById('btnCancelarRegistro');
        const formRegistro = document.getElementById('formRegistroManual');
        const btnGuardarRegistro = document.getElementById('btnGuardarRegistro');

        // Elementos Modal Excel
        const modalExcel = document.getElementById('modalExcel');
        const btnCargaExcel = document.getElementById('btnCargaExcel');
        const btnCerrarExcel = document.getElementById('btnCerrarExcel');
        const btnCancelarExcel = document.getElementById('btnCancelarExcel');
        const formExcel = document.getElementById('formCargaExcel');
        const btnProcesarExcel = document.getElementById('btnProcesarExcel');

        function toggleModal(modal, show) {
            if (!modal) return;
            modal.classList.toggle('active', show);
            modal.setAttribute('aria-hidden', !show);
            document.body.style.overflow = show ? 'hidden' : '';
        }

        // Eventos Abrir/Cerrar
        btnNuevoRegistro?.addEventListener('click', () => {
            formRegistro?.reset();
            toggleModal(modalRegistro, true);
            document.getElementById('reg_cedula')?.focus();
        });
        btnCerrarRegistro?.addEventListener('click', () => toggleModal(modalRegistro, false));
        btnCancelarRegistro?.addEventListener('click', () => toggleModal(modalRegistro, false));

        btnCargaExcel?.addEventListener('click', () => {
            formExcel?.reset();
            toggleModal(modalExcel, true);
        });
        btnCerrarExcel?.addEventListener('click', () => toggleModal(modalExcel, false));
        btnCancelarExcel?.addEventListener('click', () => toggleModal(modalExcel, false));

        // Cerrar al pulsar fondo
        [modalRegistro, modalExcel].forEach(modal => {
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) toggleModal(modal, false);
            });
        });

        // Cerrar con Escape
        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                toggleModal(modalRegistro, false);
                toggleModal(modalExcel, false);
            }
        });

        // ── Envío Formulario Registro Manual ──
        formRegistro?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const cedula = document.getElementById('reg_cedula').value.trim();
            const nombre = document.getElementById('reg_nombre').value.trim();
            const cargo = document.getElementById('reg_cargo').value.trim();
            const departamento = document.getElementById('reg_departamento').value.trim();

            if (!cedula || !nombre || !cargo || !departamento) {
                Swal.fire({
                    title: 'Campos requeridos',
                    text: 'Por favor completa todos los campos del funcionario.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
                return;
            }

            btnGuardarRegistro.disabled = true;
            btnGuardarRegistro.textContent = 'Guardando…';

            try {
                const formData = new FormData();
                formData.append('cedula', cedula);
                formData.append('nombre', nombre);
                formData.append('cargo', cargo);
                formData.append('departamento', departamento);

                const res = await fetch(BASE_URL + '/registro-manual', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json();

                if (data.exito) {
                    toggleModal(modalRegistro, false);

                    // Diálogo con visualización del QR generado y descarga
                    await Swal.fire({
                        title: '¡Funcionario Registrado!',
                        html: `
                            <p style="color:#6e6e73;font-size:0.86rem;margin-bottom:12px;">El certificado y código QR han sido generados con éxito.</p>
                            ${data.qr_base64 ? `<img src="${data.qr_base64}" alt="Código QR" style="width:160px;height:160px;border-radius:12px;margin:8px auto;display:block;box-shadow:0 4px 12px rgba(0,0,0,0.1)">` : ''}
                        `,
                        icon: 'success',
                        confirmButtonText: 'Actualizar Dashboard',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });

                    window.location.reload();
                } else {
                    Swal.fire({
                        title: 'Error al registrar',
                        text: data.mensaje || 'No se pudo completar el registro del funcionario.',
                        icon: 'error',
                        confirmButtonText: 'Revisar datos',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'Ocurrió un fallo de red al comunicar con el servidor.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
            } finally {
                btnGuardarRegistro.disabled = false;
                btnGuardarRegistro.textContent = 'Guardar y Generar QR';
            }
        });

        // ── Envío Carga Masiva Excel ──
        formExcel?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const fileInput = document.getElementById('documento_excel');
            if (!fileInput.files.length) {
                Swal.fire({
                    title: 'Archivo requerido',
                    text: 'Selecciona un archivo .xlsx para procesar la importación.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
                return;
            }

            Swal.fire({
                title: 'Procesando archivo...',
                text: 'Importando funcionarios y generando códigos QR en lote.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'apple-modal' }
            });

            const formData = new FormData(formExcel);

            try {
                const res = await fetch(BASE_URL + '/registro-excel', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json();

                if (data.estado === 'success') {
                    toggleModal(modalExcel, false);
                    await Swal.fire({
                        title: '¡Carga Completada!',
                        text: data.mensaje,
                        icon: 'success',
                        confirmButtonText: 'Continuar',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });
                    window.location.reload();
                } else {
                    Swal.fire({
                        title: 'Fallo en la importación',
                        text: data.mensaje || 'Error al procesar el archivo Excel.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                        buttonsStyling: false
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    title: 'Error de Procesamiento',
                    text: 'No se pudo completar la carga masiva. Verifica el formato del archivo.',
                    icon: 'error',
                    confirmButtonText: 'Cerrar',
                    customClass: { popup: 'apple-modal', confirmButton: 'apple-btn' },
                    buttonsStyling: false
                });
            }
        });

    })();
    </script>
</body>
</html>
