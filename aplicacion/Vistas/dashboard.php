<?php
// Extraer datos de sesión con valores por defecto seguros
$correoUsuario = htmlspecialchars($_SESSION['correo'] ?? 'usuario@ispeb.edu', ENT_QUOTES, 'UTF-8');
$rolUsuario    = htmlspecialchars($_SESSION['rol']    ?? 'Administrador',     ENT_QUOTES, 'UTF-8');

// Saludo dinámico según la hora del servidor
$hora = (int) date('H');
$saludo = match(true) {
    $hora >= 5  && $hora < 12 => 'Buenos días',
    $hora >= 12 && $hora < 19 => 'Buenas tardes',
    default                   => 'Buenas noches',
};

// Avatar: iniciales del correo
$iniciales = strtoupper(substr($correoUsuario, 0, 2));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel de control del Sistema de Certificación Institucional ISPEB. Gestiona funcionarios, códigos QR y escaneos recientes.">
    <title>Dashboard – Sistema ISPEB</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        /* ─────────────────────────────────────────────
           1. Tokens de diseño (macOS / iOS palette)
        ───────────────────────────────────────────── */
        :root {
            --bg-main:        #f5f5f7;
            --bg-sidebar:     rgba(255, 255, 255, 0.55);
            --sidebar-border: rgba(255, 255, 255, 0.80);
            --sidebar-w:      260px;

            --accent:         #0071e3;
            --accent-hover:   #0077ed;
            --accent-dark:    #004aad;

            --secondary-btn-bg:     rgba(0, 113, 227, 0.10);
            --secondary-btn-hover:  rgba(0, 113, 227, 0.18);

            --text-primary:   #1d1d1f;
            --text-secondary: #6e6e73;
            --text-muted:     #a1a1a6;

            --card-bg:        #ffffff;
            --card-radius:    20px;
            --card-shadow:    0 4px 24px rgba(0, 0, 0, 0.07), 0 1px 4px rgba(0, 0, 0, 0.04);
            --card-shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.11), 0 2px 8px rgba(0, 0, 0, 0.06);

            --nav-h:          68px;
            --radius-sm:      12px;
            --radius-md:      16px;

            --transition:     all 0.25s cubic-bezier(0.25, 0.46, 0.45, 0.94);

            /* Widget icon gradients */
            --grad-blue:   linear-gradient(135deg, #0071e3 0%, #42a5f5 100%);
            --grad-purple: linear-gradient(135deg, #7e5bef 0%, #c084fc 100%);
            --grad-green:  linear-gradient(135deg, #10b981 0%, #6ee7b7 100%);
            --grad-orange: linear-gradient(135deg, #f59e0b 0%, #fcd34d 100%);
        }

        /* ─────────────────────────────────────────────
           2. Reset & Base
        ───────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-main);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ─────────────────────────────────────────────
           3. Sidebar (Glassmorphism)
        ───────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 200;
            display: flex;
            flex-direction: column;
            background: var(--bg-sidebar);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border-right: 1px solid var(--sidebar-border);
            padding: 28px 20px 24px;
            transition: var(--transition);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 8px 32px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            margin-bottom: 28px;
        }

        .sidebar-logo .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--grad-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.35);
        }

        .sidebar-logo .logo-text {
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo .logo-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.3px;
        }

        .sidebar-logo .logo-sub {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Nav links */
        .sidebar-nav { flex: 1; display: flex; flex-direction: column; gap: 4px; }

        .nav-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            padding: 0 12px;
            margin: 20px 0 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .nav-link .nav-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            background: rgba(0,0,0,0.04);
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .nav-link:hover {
            background: rgba(0, 113, 227, 0.07);
            color: var(--accent);
        }

        .nav-link:hover .nav-icon {
            background: rgba(0, 113, 227, 0.12);
            color: var(--accent);
        }

        .nav-link.active {
            background: rgba(0, 113, 227, 0.10);
            color: var(--accent);
            font-weight: 600;
        }

        .nav-link.active .nav-icon {
            background: var(--grad-blue);
            color: white;
            box-shadow: 0 3px 10px rgba(0, 113, 227, 0.30);
        }

        /* Badge */
        .nav-badge {
            margin-left: auto;
            background: var(--accent);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
            line-height: 1.5;
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.06);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            cursor: default;
        }

        .user-avatar-sm {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--grad-blue);
            color: white;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-info-sm .user-name-sm {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .user-info-sm .user-role-sm {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ─────────────────────────────────────────────
           4. Layout principal
        ───────────────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─────────────────────────────────────────────
           5. Navbar superior
        ───────────────────────────────────────────── */
        .topnav {
            height: var(--nav-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 36px;
            background: rgba(245, 245, 247, 0.85);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topnav-left h1 {
            font-size: 19px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.4px;
        }

        .topnav-left .topnav-greeting {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 400;
            margin-top: 1px;
        }

        .topnav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Notificación dot */
        .notif-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            background: var(--card-bg);
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            color: var(--text-secondary);
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            position: relative;
        }

        .notif-btn:hover { background: white; box-shadow: 0 2px 12px rgba(0,0,0,0.12); }

        .notif-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 8px; height: 8px;
            background: #ff3b30;
            border-radius: 50%;
            border: 2px solid var(--bg-main);
        }

        /* User pill */
        .user-pill {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 6px 14px 6px 6px;
            background: var(--card-bg);
            border-radius: 30px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08);
            cursor: default;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--grad-blue);
            color: white;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-pill-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-pill-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-pill-role {
            font-size: 10px;
            color: var(--text-muted);
        }

        /* Logout button */
        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border: none;
            border-radius: 30px;
            background: rgba(255, 59, 48, 0.10);
            color: #ff3b30;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #ff3b30;
            color: white;
            box-shadow: 0 4px 14px rgba(255, 59, 48, 0.35);
            transform: translateY(-1px);
        }

        /* ─────────────────────────────────────────────
           6. Contenido principal
        ───────────────────────────────────────────── */
        .main-content {
            padding: 36px 36px 48px;
            flex: 1;
        }

        /* Section title */
        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }

        .section-sub {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 28px;
        }

        /* ─────────────────────────────────────────────
           7. Widget Grid (3 tarjetas)
        ───────────────────────────────────────────── */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .widget-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            padding: 26px 24px;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            cursor: default;
        }

        .widget-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(255,255,255,0.6) 0%, transparent 60%);
            pointer-events: none;
        }

        .widget-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-shadow-hover);
        }

        .widget-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .widget-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }

        .widget-icon.blue   { background: var(--grad-blue);   box-shadow: 0 6px 16px rgba(0,113,227,0.30); }
        .widget-icon.purple { background: var(--grad-purple);  box-shadow: 0 6px 16px rgba(126,91,239,0.30); }
        .widget-icon.green  { background: var(--grad-green);   box-shadow: 0 6px 16px rgba(16,185,129,0.30); }

        .widget-trend {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .trend-up   { background: rgba(16,185,129,0.12); color: #059669; }
        .trend-down { background: rgba(255,59,48,0.10);  color: #ff3b30; }

        .widget-value {
            font-size: 40px;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -1.5px;
            line-height: 1;
            margin-bottom: 6px;
        }

        .widget-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .widget-meta {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* ─────────────────────────────────────────────
           8. Tabla de Escaneos Recientes
        ───────────────────────────────────────────── */
        .panel-card {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 26px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .panel-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .panel-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .btn-text {
            background: none;
            border: none;
            color: var(--accent);
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .btn-text:hover { background: rgba(0,113,227,0.08); }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead th {
            text-align: left;
            padding: 12px 26px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            background: rgba(0,0,0,0.015);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        tbody tr {
            transition: background 0.15s;
        }

        tbody tr:hover { background: rgba(0,113,227,0.03); }

        tbody td {
            padding: 14px 26px;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(0,0,0,0.04);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }

        .td-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .pill-ok     { background: rgba(16,185,129,0.12); color: #059669; }
        .pill-info   { background: rgba(0,113,227,0.10);  color: #0071e3; }
        .pill-warn   { background: rgba(245,158,11,0.12); color: #d97706; }

        /* ─────────────────────────────────────────────
           9. Botones de Acción (estilo Apple)
        ───────────────────────────────────────────── */
        .actions-section {
            background: var(--card-bg);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 28px 30px;
        }

        .actions-heading {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .actions-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 22px;
        }

        .actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        /* Botón primario */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 13px 24px;
            border: none;
            border-radius: 14px;
            background: var(--accent);
            color: white;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.35);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 113, 227, 0.42);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-primary .btn-icon-wrap {
            width: 26px;
            height: 26px;
            background: rgba(255,255,255,0.20);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        /* Botón secundario */
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 13px 24px;
            border: 1.5px solid rgba(0, 113, 227, 0.25);
            border-radius: 14px;
            background: var(--secondary-btn-bg);
            color: var(--accent);
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: var(--secondary-btn-hover);
            border-color: rgba(0, 113, 227, 0.45);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 113, 227, 0.15);
        }

        .btn-secondary:active { transform: translateY(0); }

        .btn-secondary .btn-icon-wrap {
            width: 26px;
            height: 26px;
            background: rgba(0, 113, 227, 0.12);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        /* ─────────────────────────────────────────────
           10. Animación de entrada (fade + slide)
        ───────────────────────────────────────────── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeSlideUp 0.45s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
        }

        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.10s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.22s; }
        .delay-5 { animation-delay: 0.30s; }

        /* ─────────────────────────────────────────────
           11. Responsivo básico
        ───────────────────────────────────────────── */
        @media (max-width: 1100px) {
            .widgets-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            :root { --sidebar-w: 0px; }
            .sidebar { transform: translateX(-100%); }
            .main-wrapper { margin-left: 0; }
            .widgets-grid { grid-template-columns: 1fr; }
            .topnav { padding: 0 20px; }
            .main-content { padding: 24px 20px 40px; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Menú principal">

    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fa-solid fa-certificate"></i></div>
        <div class="logo-text">
            <span class="logo-title">ISPEB</span>
            <span class="logo-sub">Sistema de Certificación</span>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Navegación principal">
        <span class="nav-label">Principal</span>

        <a href="/docencia_qr/dashboard" class="nav-link active" id="nav-dashboard" aria-current="page">
            <span class="nav-icon"><i class="fa-solid fa-gauge-high"></i></span>
            Dashboard
        </a>

        <span class="nav-label">Gestión</span>

        <a href="/docencia_qr/funcionarios" class="nav-link" id="nav-funcionarios">
            <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
            Funcionarios
            <span class="nav-badge">142</span>
        </a>

        <a href="#" class="nav-link" id="nav-qrs">
            <span class="nav-icon"><i class="fa-solid fa-qrcode"></i></span>
            Códigos QR
        </a>

        <a href="#" class="nav-link" id="nav-escaneos">
            <span class="nav-icon"><i class="fa-solid fa-camera-viewfinder"></i></span>
            Escaneos
        </a>

        <span class="nav-label">Sistema</span>

        <a href="#" class="nav-link" id="nav-reportes">
            <span class="nav-icon"><i class="fa-solid fa-chart-bar"></i></span>
            Reportes
        </a>

        <a href="#" class="nav-link" id="nav-config">
            <span class="nav-icon"><i class="fa-solid fa-gear"></i></span>
            Configuración
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="user-avatar-sm"><?= $iniciales ?></div>
            <div class="user-info-sm">
                <div class="user-name-sm"><?= $correoUsuario ?></div>
                <div class="user-role-sm"><?= $rolUsuario ?></div>
            </div>
        </div>
    </div>
</aside>


<!-- ═══════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- ── Topnav ── -->
    <header class="topnav" role="banner">
        <div class="topnav-left">
            <h1><?= $saludo ?>, <?= $rolUsuario ?></h1>
            <p class="topnav-greeting">
                <i class="fa-regular fa-calendar-check" style="margin-right:5px;color:var(--accent)"></i>
                <?= date('l, d \d\e F \d\e Y') ?>
            </p>
        </div>

        <div class="topnav-right">
            <!-- Notificaciones -->
            <button class="notif-btn" id="btn-notificaciones" title="Notificaciones" aria-label="Notificaciones">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-dot"></span>
            </button>

            <!-- Pill de usuario -->
            <div class="user-pill" role="status" aria-label="Usuario actual">
                <div class="user-avatar"><?= $iniciales ?></div>
                <div class="user-pill-text">
                    <span class="user-pill-name"><?= $correoUsuario ?></span>
                    <span class="user-pill-role"><?= $rolUsuario ?></span>
                </div>
            </div>

            <!-- Cerrar Sesión -->
            <a href="/docencia_qr/logout" class="btn-logout" id="btn-cerrar-sesion" role="button">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Cerrar Sesión
            </a>
        </div>
    </header>

    <!-- ── Contenido ── -->
    <main class="main-content" role="main" id="main-content">

        <!-- Título de sección -->
        <p class="section-title animate-in">Resumen General</p>
        <p class="section-sub animate-in delay-1">Métricas actualizadas del sistema institucional.</p>

        <!-- ── Widget Grid ── -->
        <div class="widgets-grid">

            <!-- Widget 1: Funcionarios -->
            <div class="widget-card animate-in delay-1" id="widget-funcionarios">
                <div class="widget-header">
                    <div class="widget-icon blue"><i class="fa-solid fa-users"></i></div>
                    <span class="widget-trend trend-up">
                        <i class="fa-solid fa-arrow-trend-up"></i> +12%
                    </span>
                </div>
                <div class="widget-value">142</div>
                <div class="widget-label">Total Funcionarios</div>
                <div class="widget-meta">
                    <i class="fa-solid fa-circle-check" style="color:#10b981;margin-right:5px"></i>
                    138 activos &nbsp;·&nbsp; 4 inactivos
                </div>
            </div>

            <!-- Widget 2: QRs Generados -->
            <div class="widget-card animate-in delay-2" id="widget-qrs">
                <div class="widget-header">
                    <div class="widget-icon purple"><i class="fa-solid fa-qrcode"></i></div>
                    <span class="widget-trend trend-up">
                        <i class="fa-solid fa-arrow-trend-up"></i> +8%
                    </span>
                </div>
                <div class="widget-value">1,847</div>
                <div class="widget-label">QRs Generados</div>
                <div class="widget-meta">
                    <i class="fa-solid fa-clock" style="color:#7e5bef;margin-right:5px"></i>
                    Último: hace 2 horas
                </div>
            </div>

            <!-- Widget 3: Escaneos Recientes -->
            <div class="widget-card animate-in delay-3" id="widget-escaneos">
                <div class="widget-header">
                    <div class="widget-icon green"><i class="fa-solid fa-camera-viewfinder"></i></div>
                    <span class="widget-trend trend-down">
                        <i class="fa-solid fa-arrow-trend-down"></i> -3%
                    </span>
                </div>
                <div class="widget-value">324</div>
                <div class="widget-label">Escaneos Recientes</div>
                <div class="widget-meta">
                    <i class="fa-solid fa-calendar-day" style="color:#10b981;margin-right:5px"></i>
                    Este mes: 1,201 escaneos
                </div>
            </div>

        </div><!-- /widgets-grid -->

        <!-- ── Tabla de Escaneos ── -->
        <div class="panel-card animate-in delay-4" id="panel-escaneos-recientes">
            <div class="panel-header">
                <div>
                    <p class="panel-title">Escaneos Recientes</p>
                    <p class="panel-subtitle">Últimas 5 validaciones de certificado</p>
                </div>
                <button class="btn-text" id="btn-ver-todos" type="button">Ver todos →</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Funcionario</th>
                            <th>Código QR</th>
                            <th>Fecha / Hora</th>
                            <th>Dispositivo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>María González</strong></td>
                            <td><code style="font-size:11px;color:var(--text-muted)">QR-2024-0142</code></td>
                            <td>14 Oct 2026, 08:15</td>
                            <td><i class="fa-brands fa-apple" style="margin-right:4px"></i>iPhone 15</td>
                            <td><span class="td-pill pill-ok"><i class="fa-solid fa-circle-check"></i> Válido</span></td>
                        </tr>
                        <tr>
                            <td><strong>Carlos Pérez</strong></td>
                            <td><code style="font-size:11px;color:var(--text-muted)">QR-2024-0138</code></td>
                            <td>14 Oct 2026, 07:52</td>
                            <td><i class="fa-brands fa-android" style="margin-right:4px"></i>Samsung S24</td>
                            <td><span class="td-pill pill-ok"><i class="fa-solid fa-circle-check"></i> Válido</span></td>
                        </tr>
                        <tr>
                            <td><strong>Ana Rodríguez</strong></td>
                            <td><code style="font-size:11px;color:var(--text-muted)">QR-2024-0129</code></td>
                            <td>14 Oct 2026, 07:30</td>
                            <td><i class="fa-solid fa-desktop" style="margin-right:4px"></i>Web Browser</td>
                            <td><span class="td-pill pill-info"><i class="fa-solid fa-circle-info"></i> Pendiente</span></td>
                        </tr>
                        <tr>
                            <td><strong>Luis Martínez</strong></td>
                            <td><code style="font-size:11px;color:var(--text-muted)">QR-2024-0115</code></td>
                            <td>13 Oct 2026, 17:44</td>
                            <td><i class="fa-brands fa-apple" style="margin-right:4px"></i>iPad Pro</td>
                            <td><span class="td-pill pill-warn"><i class="fa-solid fa-triangle-exclamation"></i> Expirado</span></td>
                        </tr>
                        <tr>
                            <td><strong>Rosa Jiménez</strong></td>
                            <td><code style="font-size:11px;color:var(--text-muted)">QR-2024-0101</code></td>
                            <td>13 Oct 2026, 14:18</td>
                            <td><i class="fa-brands fa-android" style="margin-right:4px"></i>Pixel 8</td>
                            <td><span class="td-pill pill-ok"><i class="fa-solid fa-circle-check"></i> Válido</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Botones de Acción ── -->
        <div class="actions-section animate-in delay-5" id="acciones-principales">
            <p class="actions-heading">Acciones Rápidas</p>
            <p class="actions-desc">Gestiona funcionarios y genera certificados QR desde aquí.</p>

            <div class="actions-row">
                <!-- Primario: Añadir Funcionario -->
                <button class="btn-primary" type="button" id="btn-anadir-funcionario">
                    <span class="btn-icon-wrap"><i class="fa-solid fa-user-plus"></i></span>
                    Añadir Funcionario (Manual)
                </button>

                <!-- Secundario: Carga Masiva -->
                <button class="btn-secondary" type="button" id="btn-carga-masiva">
                    <span class="btn-icon-wrap"><i class="fa-solid fa-file-excel"></i></span>
                    Carga Masiva (Excel)
                </button>

                <!-- Extra: Generar QR -->
                <button class="btn-secondary" type="button" id="btn-generar-qr">
                    <span class="btn-icon-wrap"><i class="fa-solid fa-qrcode"></i></span>
                    Generar QR
                </button>

                <!-- Descargar QRs (ZIP) -->
                <a href="/docencia_qr/descargar-qrs" class="btn-secondary" id="btn-descargar-qrs">
                    <span class="btn-icon-wrap"><i class="fa-solid fa-file-zipper"></i></span>
                    Descargar QRs (ZIP)
                </a>

                <!-- Respaldar Base de Datos -->
                <a href="/docencia_qr/respaldo-bd" class="btn-secondary" id="btn-respaldar-bd">
                    <span class="btn-icon-wrap"><i class="fa-solid fa-database"></i></span>
                    Respaldar Base de Datos
                </a>
            </div>
        </div>

    </main><!-- /main-content -->
</div><!-- /main-wrapper -->


<!-- ═══════════════════════════════════════════════════════════════
     MODAL: AÑADIR FUNCIONARIO (estilo iOS / macOS)
     Actívalo con: document.getElementById('modal-funcionario').classList.add('open')
════════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="modal-funcionario" role="dialog" aria-modal="true"
     aria-labelledby="modal-titulo" aria-describedby="modal-desc">

    <div class="modal-card" id="modal-card-funcionario">

        <!-- Cabecera del modal -->
        <div class="modal-header">
            <div class="modal-icon-wrap">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <div>
                <h2 class="modal-title" id="modal-titulo">Añadir Funcionario</h2>
                <p class="modal-subtitle" id="modal-desc">Registro manual · Se generará un QR único automáticamente.</p>
            </div>
            <button class="modal-close" id="btn-modal-close" type="button" aria-label="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Formulario -->
        <form id="form-registro-manual" novalidate autocomplete="off">

            <div class="modal-body">

                <!-- Cédula -->
                <div class="field-group">
                    <label class="field-label" for="campo-cedula">
                        <i class="fa-solid fa-id-card"></i> Cédula de Identidad
                    </label>
                    <input class="field-input" type="text" id="campo-cedula" name="cedula"
                           placeholder="Ej: V-12345678" maxlength="20" required
                           inputmode="text" autocomplete="off">
                    <span class="field-hint">Sin puntos ni guiones intermedios.</span>
                </div>

                <!-- Nombre Completo -->
                <div class="field-group">
                    <label class="field-label" for="campo-nombre">
                        <i class="fa-solid fa-user"></i> Nombre Completo
                    </label>
                    <input class="field-input" type="text" id="campo-nombre" name="nombre"
                           placeholder="Ej: María González Pérez" maxlength="150" required
                           autocomplete="off">
                </div>

                <!-- Cargo -->
                <div class="field-group">
                    <label class="field-label" for="campo-cargo">
                        <i class="fa-solid fa-briefcase"></i> Cargo
                    </label>
                    <input class="field-input" type="text" id="campo-cargo" name="cargo"
                           placeholder="Ej: Docente de Matemáticas" maxlength="150" required
                           autocomplete="off">
                </div>

                <!-- Departamento -->
                <div class="field-group">
                    <label class="field-label" for="campo-departamento">
                        <i class="fa-solid fa-building"></i> Departamento
                    </label>
                    <input class="field-input" type="text" id="campo-departamento" name="departamento"
                           placeholder="Ej: Ciencias Exactas" maxlength="150" required
                           autocomplete="off">
                </div>

            </div><!-- /modal-body -->

            <!-- Footer con acciones -->
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn-modal-cancelar">
                    Cancelar
                </button>
                <button type="submit" class="btn-modal-submit" id="btn-modal-submit">
                    <span class="submit-icon"><i class="fa-solid fa-qrcode"></i></span>
                    <span class="submit-text">Registrar y Generar QR</span>
                    <span class="submit-loader" aria-hidden="true"></span>
                </button>
            </div>

        </form>
    </div><!-- /modal-card -->
</div><!-- /modal-backdrop -->

<!-- ═══════════════════════════════════════════════════════════════
     MODAL: CARGA MASIVA EXCEL (estilo iOS / macOS)
════════════════════════════════════════════════════════════════ -->
<div class="modal-backdrop" id="modal-carga-masiva" role="dialog" aria-modal="true"
     aria-labelledby="modal-excel-titulo" aria-describedby="modal-excel-desc">

    <div class="modal-card" id="modal-card-excel">

        <!-- Cabecera del modal -->
        <div class="modal-header">
            <div class="modal-icon-wrap" style="background: linear-gradient(135deg, #10b981, #34d399); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);">
                <i class="fa-solid fa-file-excel"></i>
            </div>
            <div>
                <h2 class="modal-title" id="modal-excel-titulo">Carga Masiva de Funcionarios</h2>
                <p class="modal-subtitle" id="modal-excel-desc">Importación masiva (.xlsx) con generación de QR en lote.</p>
            </div>
            <button class="modal-close" id="btn-excel-close" type="button" aria-label="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Formulario Carga Masiva -->
        <form id="formCargaMasiva" enctype="multipart/form-data" novalidate autocomplete="off">

            <div class="modal-body">

                <!-- Aviso de estructura -->
                <div style="background: rgba(0, 113, 227, 0.06); border: 1px solid rgba(0, 113, 227, 0.15); border-radius: 12px; padding: 14px 16px; font-size: 13px; color: #1d1d1f; line-height: 1.5;">
                    <div style="font-weight: 600; color: #0071e3; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Estructura del Archivo
                    </div>
                    La fila 1 debe contener los encabezados: <strong>Cédula (A), Nombre (B), Cargo (C), Departamento (D)</strong>. Los registros se leerán a partir de la fila 2.
                </div>

                <!-- Selector de archivo -->
                <div class="field-group">
                    <label class="field-label" for="campo-documento-excel">
                        <i class="fa-solid fa-file-arrow-up"></i> Selecciona el Archivo Excel (.xlsx)
                    </label>
                    <input class="field-input" type="file" id="campo-documento-excel" name="documento_excel"
                           accept=".xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                    <span class="field-hint">Solo formato .xlsx (Microsoft Excel OpenXML).</span>
                </div>

            </div><!-- /modal-body -->

            <!-- Footer con acciones -->
            <div class="modal-footer">
                <button type="button" class="btn-modal-cancel" id="btn-excel-cancelar">
                    Cancelar
                </button>
                <button type="submit" class="btn-modal-submit" id="btn-excel-submit" style="background: #10b981; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);">
                    <span class="submit-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="submit-text">Procesar Archivo Excel</span>
                    <span class="submit-loader" aria-hidden="true"></span>
                </button>
            </div>

        </form>
    </div><!-- /modal-card -->
</div><!-- /modal-backdrop -->


<!-- ═══════════════════════════════
     CSS DEL MODAL
════════════════════════════════ -->
<style>
    /* ── Backdrop ── */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 900;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(10, 10, 20, 0.45);
        backdrop-filter: blur(12px) saturate(160%);
        -webkit-backdrop-filter: blur(12px) saturate(160%);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.28s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .modal-backdrop.open {
        opacity: 1;
        pointer-events: all;
    }

    /* ── Tarjeta modal ── */
    .modal-card {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 520px;
        box-shadow:
            0 32px 80px rgba(0, 0, 0, 0.22),
            0 8px 24px  rgba(0, 0, 0, 0.10),
            0 0   0 1px rgba(255,255,255,0.08) inset;
        transform: translateY(24px) scale(0.97);
        transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1),
                    opacity  0.28s ease;
        opacity: 0;
        overflow: hidden;
    }

    .modal-backdrop.open .modal-card {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* ── Header ── */
    .modal-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 24px 24px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .modal-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0071e3, #42a5f5);
        color: white;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0,113,227,0.35);
    }

    .modal-title {
        font-size: 17px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.3px;
        margin: 0;
    }

    .modal-subtitle {
        font-size: 12px;
        color: #a1a1a6;
        margin: 3px 0 0;
    }

    .modal-close {
        margin-left: auto;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: rgba(0,0,0,0.06);
        color: #6e6e73;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        flex-shrink: 0;
    }

    .modal-close:hover {
        background: rgba(255,59,48,0.12);
        color: #ff3b30;
    }

    /* ── Body / Campos ── */
    .modal-body {
        padding: 22px 24px 8px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field-label {
        font-size: 12px;
        font-weight: 600;
        color: #6e6e73;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .field-input {
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid rgba(0,0,0,0.10);
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        color: #1d1d1f;
        background: #f5f5f7;
        outline: none;
        transition: all 0.22s;
        -webkit-appearance: none;
    }

    .field-input:focus {
        border-color: #0071e3;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
    }

    .field-input.is-invalid {
        border-color: #ff3b30;
        background: #fff5f5;
        box-shadow: 0 0 0 3px rgba(255, 59, 48, 0.12);
    }

    .field-input::placeholder { color: #c7c7cc; }

    .field-hint {
        font-size: 11px;
        color: #a1a1a6;
    }

    /* ── Footer ── */
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        padding: 20px 24px 24px;
        border-top: 1px solid rgba(0,0,0,0.05);
        margin-top: 10px;
    }

    .btn-modal-cancel {
        padding: 11px 20px;
        border: 1.5px solid rgba(0,0,0,0.10);
        border-radius: 12px;
        background: transparent;
        color: #6e6e73;
        font-size: 14px;
        font-weight: 500;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-modal-cancel:hover {
        background: rgba(0,0,0,0.04);
        border-color: rgba(0,0,0,0.18);
    }

    .btn-modal-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border: none;
        border-radius: 12px;
        background: #0071e3;
        color: white;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 4px 14px rgba(0,113,227,0.35);
        position: relative;
        overflow: hidden;
        min-width: 190px;
        justify-content: center;
    }

    .btn-modal-submit:hover:not(:disabled) {
        background: #0077ed;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0,113,227,0.42);
    }

    .btn-modal-submit:disabled {
        background: #6e6e73;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .submit-icon {
        width: 22px;
        height: 22px;
        background: rgba(255,255,255,0.18);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    /* Spinner del botón */
    .submit-loader {
        display: none;
        width: 16px;
        height: 16px;
        border: 2.5px solid rgba(255,255,255,0.35);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .btn-modal-submit.loading .submit-icon,
    .btn-modal-submit.loading .submit-text { display: none; }

    .btn-modal-submit.loading .submit-loader { display: block; }
    .btn-modal-submit.loading { min-width: 54px; padding: 11px 16px; }
</style>


<!-- ═══════════════════════════════════════════════════════════════
     SCRIPTS: SweetAlert2 + Fetch AJAX para el formulario de registro
════════════════════════════════════════════════════════════════ -->

<!-- SweetAlert2 v11 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<script>
(function () {
    'use strict';

    // ── Referencias DOM ───────────────────────────────────────────────────────
    const modalBackdrop    = document.getElementById('modal-funcionario');
    const btnAbrirModal    = document.getElementById('btn-anadir-funcionario');
    const btnCerrarX       = document.getElementById('btn-modal-close');
    const btnCancelar      = document.getElementById('btn-modal-cancelar');
    const formRegistro     = document.getElementById('form-registro-manual');
    const btnSubmit        = document.getElementById('btn-modal-submit');

    // ── Abrir / Cerrar Modal ──────────────────────────────────────────────────
    function abrirModal() {
        modalBackdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
        // Foco en el primer campo para accesibilidad
        setTimeout(() => document.getElementById('campo-cedula')?.focus(), 320);
    }

    function cerrarModal() {
        modalBackdrop.classList.remove('open');
        document.body.style.overflow = '';
        formRegistro.reset();
        // Limpiar estados de error visuales
        formRegistro.querySelectorAll('.field-input').forEach(el => el.classList.remove('is-invalid'));
        // Restaurar botón
        btnSubmit.classList.remove('loading');
        btnSubmit.disabled = false;
    }

    btnAbrirModal?.addEventListener('click', abrirModal);
    btnCerrarX?.addEventListener('click', cerrarModal);
    btnCancelar?.addEventListener('click', cerrarModal);

    // Cerrar al hacer clic en el fondo del backdrop
    modalBackdrop?.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) cerrarModal();
    });

    // Cerrar con Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('open')) cerrarModal();
    });

    // ── Validación cliente ────────────────────────────────────────────────────
    function validarCampos() {
        let valido = true;
        const campos = formRegistro.querySelectorAll('[required]');

        campos.forEach(campo => {
            if (!campo.value.trim()) {
                campo.classList.add('is-invalid');
                valido = false;
            } else {
                campo.classList.remove('is-invalid');
            }
        });

        return valido;
    }

    // ── Envío AJAX via Fetch API ──────────────────────────────────────────────
    formRegistro?.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validarCampos()) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor completa todos los campos antes de continuar.',
                confirmButtonColor: '#0071e3',
                confirmButtonText: 'Entendido',
                borderRadius: '18px',
                customClass: {
                    popup: 'swal-ios-popup',
                    confirmButton: 'swal-ios-btn',
                },
            });
            return;
        }

        // ── Estado de carga en el botón ───────────────────────────────────
        btnSubmit.classList.add('loading');
        btnSubmit.disabled = true;

        const formData = new FormData(formRegistro);

        try {
            const response = await fetch('/docencia_qr/registro-manual', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            // Verificar que la respuesta es JSON válido
            const contentType = response.headers.get('content-type') ?? '';
            if (!contentType.includes('application/json')) {
                throw new Error(`Respuesta inesperada del servidor (${response.status}).`);
            }

            const data = await response.json();

            if (data.exito) {
                cerrarModal();

                // ── Alerta de ÉXITO con QR embebido en Base64 (sin ruta web) ──
                await Swal.fire({
                    icon: 'success',
                    title: '¡Registro exitoso!',
                    html: `<p style="color:#6e6e73;font-size:14px;margin:0 0 4px">
                               ${data.mensaje ?? 'El funcionario fue registrado correctamente.'}
                           </p>
                           <p style="color:#a1a1a6;font-size:12px;margin:0">
                               Escanea el QR para validar el certificado.
                           </p>`,
                    // imageUrl acepta Data URIs base64 → sin ruta web ni .htaccess
                    imageUrl:    data.qr_base64 ?? null,
                    imageWidth:  180,
                    imageHeight: 180,
                    imageAlt:    'Código QR del funcionario',
                    showDenyButton:    !!data.qr_base64,
                    denyButtonText:    '<i class="fa-solid fa-download" style="margin-right:6px"></i>Descargar QR',
                    denyButtonColor:   '#6e6e73',
                    confirmButtonText:  'Perfecto',
                    confirmButtonColor: '#0071e3',
                    // Al pulsar "Descargar QR" descarga el PNG desde Base64 sin recargar
                    preDeny: () => {
                        if (data.qr_base64) {
                            const link    = document.createElement('a');
                            link.href     = data.qr_base64;
                            link.download = `qr_funcionario_${(data.token ?? 'ispeb').slice(0, 8)}.png`;
                            link.click();
                        }
                        return false; // Mantiene el modal abierto tras descargar
                    },
                });


            } else {
                // ── Alerta de ERROR (cédula duplicada u otro) ─────────────
                const esDuplicado = (data.mensaje ?? '').toLowerCase().includes('cédula');

                Swal.fire({
                    icon: esDuplicado ? 'warning' : 'error',
                    title: esDuplicado ? 'Cédula duplicada' : 'Error al registrar',
                    text: data.mensaje ?? 'Ocurrió un error al procesar la solicitud.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: esDuplicado ? '#f59e0b' : '#ff3b30',
                });
            }

        } catch (err) {
            console.error('[ISPEB] Error en fetch registro-manual:', err);

            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor. Verifica tu conexión e inténtalo nuevamente.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#ff3b30',
            });
        } finally {
            // ── Restaurar botón siempre ───────────────────────────────────
            btnSubmit.classList.remove('loading');
            btnSubmit.disabled = false;
        }
    });

    // ── Limpiar marcas de error al escribir ───────────────────────────────────
    formRegistro?.querySelectorAll('.field-input').forEach(campo => {
        campo.addEventListener('input', () => campo.classList.remove('is-invalid'));
    });

    // ══════════════════════════════════════════════════════════════════════════
    // MODAL Y CARGA MASIVA EXCEL (#formCargaMasiva)
    // ══════════════════════════════════════════════════════════════════════════
    const modalExcelBackdrop = document.getElementById('modal-carga-masiva');
    const btnAbrirExcel      = document.getElementById('btn-carga-masiva');
    const btnCerrarExcelX    = document.getElementById('btn-excel-close');
    const btnCancelarExcel   = document.getElementById('btn-excel-cancelar');
    const formCargaMasiva    = document.getElementById('formCargaMasiva');
    const btnSubmitExcel     = document.getElementById('btn-excel-submit');

    function abrirModalExcel() {
        modalExcelBackdrop?.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalExcel() {
        modalExcelBackdrop?.classList.remove('open');
        document.body.style.overflow = '';
        formCargaMasiva?.reset();
        btnSubmitExcel?.classList.remove('loading');
        if (btnSubmitExcel) btnSubmitExcel.disabled = false;
    }

    btnAbrirExcel?.addEventListener('click', abrirModalExcel);
    btnCerrarExcelX?.addEventListener('click', cerrarModalExcel);
    btnCancelarExcel?.addEventListener('click', cerrarModalExcel);

    modalExcelBackdrop?.addEventListener('click', (e) => {
        if (e.target === modalExcelBackdrop) cerrarModalExcel();
    });

    // Interceptar envío del formulario de Excel con Fetch API
    formCargaMasiva?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const inputArchivo = document.getElementById('campo-documento-excel') || formCargaMasiva.querySelector('input[name="documento_excel"]');
        if (!inputArchivo || !inputArchivo.files || inputArchivo.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo no seleccionado',
                text: 'Por favor selecciona un archivo Excel (.xlsx) antes de continuar.',
                confirmButtonColor: '#0071e3',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        const archivo = inputArchivo.files[0];
        if (!archivo.name.toLowerCase().endsWith('.xlsx')) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no permitido',
                text: 'El archivo seleccionado debe tener extensión .xlsx.',
                confirmButtonColor: '#ff3b30',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        // 1. Mostrar SweetAlert de procesamiento sin permitir cierre
        Swal.fire({
            title: 'Procesando archivo, no cierre la ventana...',
            html: '<p style="color:#6e6e73;font-size:14px;margin-top:8px">Cargando funcionarios a la base de datos y generando códigos QR institucionales...</p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 2. Preparar FormData
        const formData = new FormData(formCargaMasiva);

        // Ruta de envío
        const rutaEndpoint = window.location.pathname.includes('/docencia_qr')
            ? '/docencia_qr/registro-excel'
            : '/registro-excel';

        try {
            // 3. Envío asíncrono con Fetch API
            const response = await fetch(rutaEndpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            // 4. Mostrar resumen devuelto por el servidor
            if (data.estado === 'success') {
                cerrarModalExcel();
                await Swal.fire({
                    icon: 'success',
                    title: '¡Carga Completada!',
                    text: data.mensaje,
                    confirmButtonColor: '#0071e3',
                    confirmButtonText: 'Aceptar'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la carga masiva',
                    text: data.mensaje || 'Ocurrió un error al procesar el archivo Excel.',
                    confirmButtonColor: '#ff3b30',
                    confirmButtonText: 'Aceptar'
                });
            }
        } catch (error) {
            console.error('[ISPEB] Error en fetch registro-excel:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo comunicar con el servidor o el archivo causó un fallo inesperado.',
                confirmButtonColor: '#ff3b30',
                confirmButtonText: 'Aceptar'
            });
        }
    });

})();
</script>

</body>
</html>
