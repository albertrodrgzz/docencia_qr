<?php
/**
 * Plantilla Maestra: Header (El Ensamblador) — ISPEB Sistema Institucional
 * Ecosistema iPadOS / macOS con CSS Puro de Alta Gama
 *
 * @var string|null $tituloVista       Título de la vista (para <title> y <h1>)
 * @var bool|null   $mostrarBotonAtras Define si se muestra el botón circular de regresar
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) ? '/docencia_qr' : '');
}

$tituloPagina = htmlspecialchars($tituloVista ?? 'Sistema ISPEB', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f5f7">
    <meta name="description" content="Sistema Institucional de Certificaciones y Validación QR — Kredentia">
    <title>Kredentia | Autenticación Institucional</title>

    <!-- Favicon Kredentia -->
    <link rel="icon" type="image/png" href="<?= (defined('BASE_URL') && BASE_URL !== '') ? BASE_URL . '/' : '' ?>assets/img/favicon.png">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        /* ── Variables de Diseño Neo-Corporate / Apple ── */
        :root {
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", sans-serif;
            
            /* Color Principal: Índigo Profundo Neo-Corporate */
            --primary-indigo: #3730a3;
            --primary-indigo-hover: #312e81;
            --primary-indigo-light: #4338ca;
            --primary-indigo-tint: rgba(55, 48, 163, 0.08);
            --primary-indigo-glow: rgba(55, 48, 163, 0.22);
            
            /* Compatibilidad y Acentos */
            --apple-blue: #3730a3;
            --apple-blue-hover: #312e81;
            --apple-blue-tint: rgba(55, 48, 163, 0.08);
            --apple-purple: #7c3aed;
            --apple-purple-tint: rgba(124, 58, 237, 0.10);
            --apple-green: #10b981;
            --apple-green-tint: rgba(16, 185, 129, 0.12);
            --apple-emerald: #059669;
            --apple-emerald-tint: rgba(5, 150, 105, 0.10);
            
            --bg-page: #fbfbfe;
            --bg-card: #ffffff;
            --text-heading: #0f172a;
            --text-secondary: #64748b;
            --border-subtle: rgba(15, 23, 42, 0.06);
            --sidebar-width: 260px;
            --radius-card: 20px;
            --radius-control: 12px;
            --radius-pill: 9999px;
            --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.03);
            --shadow-card: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        /* ── Reseteo y Base Tipográfica ── */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(rgba(79, 70, 229, 0.08) 2px, transparent 2px), 
                radial-gradient(rgba(6, 182, 212, 0.05) 1px, transparent 1px);
            background-size: 32px 32px, 16px 16px;
            background-position: 0 0, 8px 8px;
            background-attachment: fixed;
            font-family: var(--font-family);
            color: var(--text-heading);
            letter-spacing: -0.015em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Títulos: Inter con letter-spacing: -0.03em */
        h1, h2, h3, h4, h5, h6, .page-title, .dashboard-main-title {
            font-family: var(--font-family);
            letter-spacing: -0.03em;
            color: var(--text-heading);
            font-weight: 700;
        }

        /* ── 1. Sidebar Fijo a la Izquierda (260px) ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border-right: 1px solid rgba(15, 23, 42, 0.06);
            padding: 24px 16px 20px 16px;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-sizing: border-box;
            box-shadow: 2px 0 24px rgba(15, 23, 42, 0.02);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px 20px 14px;
            text-decoration: none;
            color: var(--text-heading);
            border-bottom: 1px solid var(--border-subtle);
            margin-bottom: 16px;
            animation: none !important;
            transform: none !important;
            transition: opacity 0.2s ease;
        }

        .sidebar-brand:hover {
            opacity: 0.88;
            transform: none !important;
            background: transparent !important;
        }

        .sidebar-brand-img {
            max-width: 170px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            object-fit: contain;
            user-select: none;
            -webkit-user-drag: none;
            transition: transform 0.2s ease;
        }

        .sidebar-brand-img:hover {
            transform: scale(1.02);
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-grow: 1;
        }

        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #86868b;
            margin: 25px 0 10px 20px;
            padding: 0;
            display: block;
        }

        .sidebar a {
            font-family: var(--font-family);
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: -0.015em;
            color: #475569;
            padding: 11px 16px;
            border-radius: 14px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            display: flex;
            align-items: center;
            text-decoration: none;
            box-sizing: border-box;
            position: relative;
        }

        .sidebar a:hover {
            background: rgba(15, 23, 42, 0.04);
            transform: translateX(4px);
            color: var(--text-heading);
        }

        /* Ítem Activo: Fondo índigo #3730a3, texto blanco y esquinas redondeadas */
        .sidebar a.active {
            background: var(--primary-indigo, #3730a3) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(55, 48, 163, 0.25);
            transform: none !important;
        }

        .sidebar a.active:hover {
            transform: none !important;
            background: var(--primary-indigo-hover, #312e81) !important;
            color: #ffffff !important;
        }

        .sidebar-brand {
            animation: none !important;
            transform: none !important;
        }

        .sidebar-brand:hover {
            transform: none !important;
            background: transparent !important;
        }

        .sidebar a i {
            width: 24px;
            font-size: 1.15rem;
            text-align: center;
            margin-right: 12px;
            color: #94a3b8;
            transition: color 0.2s ease;
            display: inline-block;
            flex-shrink: 0;
        }

        .sidebar a.active i {
            color: #ffffff !important;
        }

        /* ── Indicador de Red en Tiempo Real (.network-status) ── */
        .network-status {
            margin-top: auto;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 12px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: all 0.25s ease;
            user-select: none;
        }

        .network-status:hover {
            background: #ffffff;
            border-color: rgba(15, 23, 42, 0.12);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Estado Online: Punto Verde con resplandor suave */
        .network-status.online .status-dot {
            background-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
            animation: pulseOnline 2.5s infinite ease-in-out;
        }

        /* Estado Offline: Punto Rojo con resplandor de advertencia */
        .network-status.offline .status-dot {
            background-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25);
            animation: pulseOffline 1.5s infinite ease-in-out;
        }

        @keyframes pulseStatusGreen {
            0%, 100% {
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.12);
                transform: scale(1.08);
            }
        }

        .status-text {
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            letter-spacing: -0.01em;
            line-height: 1.2;
            transition: color 0.25s ease;
        }

        .network-status.offline .status-text {
            color: #ef4444;
            font-weight: 600;
        }

        /* ── 2. Contenedor Principal (.main-wrapper) ── */
        .main-wrapper {
            margin-left: var(--sidebar-width, 260px);
            min-height: 100vh;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(rgba(79, 70, 229, 0.08) 2px, transparent 2px), 
                radial-gradient(rgba(6, 182, 212, 0.05) 1px, transparent 1px);
            background-size: 32px 32px, 16px 16px;
            background-position: 0 0, 8px 8px;
            background-attachment: fixed;
            transition: margin-left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        /* ── 3. Topbar (.topbar sticky con blur y ancho 100%) ── */
        .topbar {
            position: sticky;
            top: 0;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 32px;
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            z-index: 90;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Flecha de retroceso tenue */
        .btn-back-subtle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(15, 23, 42, 0.035);
            border: 1px solid rgba(15, 23, 42, 0.06);
            color: #94a3b8;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            outline: none;
        }

        .btn-back-subtle:hover {
            background: rgba(55, 48, 163, 0.08);
            color: var(--primary-indigo, #3730a3);
            border-color: rgba(55, 48, 163, 0.22);
            transform: translateX(-2px);
        }

        .btn-back-subtle i {
            font-size: 0.85rem;
        }

        .topbar-divider {
            color: #cbd5e1;
            font-weight: 300;
            font-size: 1.05rem;
            user-select: none;
            margin: 0 2px;
        }

        .topbar-caps-tag {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .topbar-left .page-title,
        .topbar-left h1,
        .page-title {
            font-family: var(--font-family);
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-heading);
            margin: 0;
            line-height: 1.15;
            text-shadow: 0px 4px 15px rgba(0, 0, 0, 0.04);
        }

        /* ── 4. Contenedor de Contenido (.content-wrapper) ── */
        .content-wrapper {
            padding: 30px;
            box-sizing: border-box;
        }

        /* Botón Atrás legado */
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            border: none;
            color: var(--text-heading);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.16s ease;
            outline: none;
        }

        .btn-back:hover {
            background: rgba(0, 0, 0, 0.10);
            transform: scale(1.05);
        }

        .btn-back i {
            font-size: 0.88rem;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        /* Píldora redondeada de perfil de usuario */
        .user-pill-badge,
        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 14px 5px 6px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: var(--radius-pill);
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.02);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .user-pill-badge:hover,
        .user-profile-badge:hover {
            background-color: #ffffff;
            border-color: rgba(55, 48, 163, 0.25);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(55, 48, 163, 0.08);
        }

        .user-avatar-circle,
        .user-avatar-initials {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-indigo), #4338ca);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.76rem;
            display: flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0.02em;
            box-shadow: 0 2px 8px rgba(55, 48, 163, 0.30);
            flex-shrink: 0;
        }

        .user-pill-text,
        .user-text-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-pill-name,
        .user-text-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-heading);
            letter-spacing: -0.01em;
        }

        .user-pill-role,
        .user-text-role {
            font-size: 0.69rem;
            font-weight: 500;
            color: #64748b;
        }

        .user-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
            margin-left: 2px;
            flex-shrink: 0;
        }

        .btn-topbar-logout {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 34px;
            padding: 0 14px;
            border-radius: var(--radius-control);
            text-decoration: none;
            font-size: 0.80rem;
            font-weight: 500;
            color: #c93b2b;
            background: rgba(201, 59, 43, 0.08);
            border: none;
            transition: all 0.16s ease;
        }

        .btn-topbar-logout:hover {
            background: rgba(201, 59, 43, 0.15);
            color: #b32617;
            transform: translateY(-1px);
        }

        /* ── Animaciones Modernas (Keyframes) ── */
        @keyframes fadeUpSmooth {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInRight {
            0% { opacity: 0; transform: translateX(-15px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        @keyframes highlightNewRow {
            0% {
                background-color: rgba(0, 113, 227, 0.22);
                opacity: 0;
                transform: translateY(-8px);
            }
            60% {
                background-color: rgba(0, 113, 227, 0.10);
            }
            100% {
                background-color: transparent;
                opacity: 1;
                transform: translateY(0);
            }
        }

        .row-highlight-new {
            animation: highlightNewRow 1.4s ease-out forwards !important;
        }

        /* Aplicar animación de entrada únicamente a las tarjetas para evitar romper position: fixed en el layout */
        .content-card {
            animation: fadeUpSmooth 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ── Modales Globales Estilo Neo-Corporate Apple ── */
        .modal-overlay,
        .modal-backdrop-ios {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999 !important;
            background: rgba(0, 0, 0, 0.40) !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            animation: fadeIn 0.3s ease-out forwards;
        }

        .modal-content,
        .modal-card-ios {
            animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* ── 4. Contenedor Flotante (.content-card) ── */
        .content-card {
            background: var(--bg-card);
            border-radius: var(--radius-card);
            padding: 32px 36px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.04);
            margin-bottom: 30px;
            box-sizing: border-box;
        }

        /* ── 5. Tablas Estilo iOS (Calidad Premium) ── */
        .table-container,
        .table-container-responsive,
        .table-responsive,
        .clean-table-container,
        .ios-table-container {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.04);
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
            margin-top: 16px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
        }

        table,
        .table-minimal-ios,
        .users-table,
        .clean-table {
            width: 100%;
            border-collapse: collapse;
            font-family: var(--font-family);
            text-align: left;
            border: none;
            background: #ffffff;
        }

        /* Cabeceras (th): Texto gris (#86868b), mayúsculas, 0.75rem, letter-spacing: 0.06em, fondo ultra claro #fafafa */
        table thead th,
        .table-minimal-ios thead th,
        .users-table thead th,
        .clean-table thead th {
            color: #86868b;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            background-color: #fafafa;
            padding: 14px 20px;
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            white-space: nowrap;
        }

        /* Filas (tr): Transición suave, animación de entrada fadeUp y hover #f5f5f7 */
        table tbody tr,
        .table-minimal-ios tbody tr,
        .users-table tbody tr,
        .clean-table tbody tr {
            transition: background 0.2s ease;
            animation: fadeUp 0.4s ease-out forwards;
        }

        table tbody tr:hover,
        .table-minimal-ios tbody tr:hover,
        .users-table tbody tr:hover,
        .clean-table tbody tr:hover {
            background-color: #f5f5f7;
        }

        /* Celdas (td): Sin bordes verticales, línea horizontal finísima, padding amplio */
        table tbody td,
        .table-minimal-ios tbody td,
        .users-table tbody td,
        .clean-table tbody td {
            border-left: none;
            border-right: none;
            border-top: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 0.90rem;
            color: #1d1d1f;
        }

        /* Última fila sin borde inferior */
        table tbody tr:last-child td,
        .table-minimal-ios tbody tr:last-child td,
        .users-table tbody tr:last-child td,
        .clean-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Texto principal de la celda (oscuro y en negrita 500) */
        .cell-main-text,
        .cell-nombre,
        .user-name,
        table tbody td strong,
        table tbody td b,
        table tbody td .main-text {
            display: block;
            color: #1d1d1f;
            font-weight: 500;
            font-size: 0.93rem;
            letter-spacing: -0.01em;
        }

        /* Subtítulo de la celda (más pequeño y gris) */
        .cell-sub-text,
        .cell-subtext,
        .cell-cargo,
        .cell-depto,
        .user-email,
        table tbody td small,
        table tbody td .sub-text,
        table tbody td .text-secondary {
            display: block;
            color: #86868b;
            font-size: 0.78rem;
            font-weight: 400;
            margin-top: 3px;
            letter-spacing: 0;
        }

        /* Responsividad para pantallas móviles / tablets */
        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }

        /* ── Físicas de Entrada y Salida de Modales Estilo iOS / macOS ── */
        @keyframes fadeInBackdrop {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes popInModal {
            0% {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes fadeOutBackdrop {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        @keyframes popOutModal {
            0% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
            100% {
                opacity: 0;
                transform: scale(0.95) translateY(10px);
            }
        }

        /* ── Blindaje y Animación del Fondo Oscuro (Cubre el 100% del Viewport) ── */
        .modal,
        .modal-overlay,
        .modal-backdrop, 
        .modal-backdrop-ios, 
        .overlay,
        dialog::backdrop {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important; /* 100% del ancho de la ventana */
            height: 100vh !important; /* 100% del alto de la ventana */
            margin: 0 !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            z-index: 9999 !important; /* Supremo, por encima del topbar y sidebar */
            display: flex;
            justify-content: center;
            align-items: center;
            
            /* El diseño de cristal oscuro de Apple */
            background: rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            animation: fadeInBackdrop 0.3s ease-out forwards;
        }

        /* ── La caja blanca central del modal (Tarjeta Flotante) ── */
        .modal-content, 
        .modal-dialog, 
        .modal-card-ios, 
        .modal-card {
            position: relative;
            z-index: 10000 !important;
            width: 100%;
            max-width: 500px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-height: 90vh;
            overflow-y: auto;
            animation: popInModal 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            will-change: transform, opacity;
        }

        /* ── Transiciones de Salida (.fade-out o .closing) ── */
        .modal-backdrop.fade-out,
        .modal-overlay.fade-out,
        .modal-backdrop-ios.fade-out,
        .overlay.fade-out,
        .modal-backdrop.closing,
        .modal-overlay.closing,
        .modal-backdrop-ios.closing,
        .overlay.closing {
            animation: fadeOutBackdrop 0.25s ease-in forwards !important;
        }

        .modal-content.fade-out,
        .modal-dialog.fade-out,
        .modal-card-ios.fade-out,
        .modal-card.fade-out,
        .fade-out .modal-content,
        .fade-out .modal-dialog,
        .fade-out .modal-card-ios,
        .fade-out .modal-card,
        .modal-content.closing,
        .modal-dialog.closing,
        .modal-card-ios.closing,
        .modal-card.closing,
        .closing .modal-content,
        .closing .modal-dialog,
        .closing .modal-card-ios,
        .closing .modal-card {
            animation: popOutModal 0.25s ease-in forwards !important;
        }

        /* Para sobreescribir el fondo por defecto de SweetAlert2 */
        div:where(.swal2-container).swal2-backdrop-show, 
        div:where(.swal2-container).swal2-noanimation {
            background: rgba(0, 0, 0, 0.4) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px) !important;
            z-index: 9998 !important;
        }

        /* ── Estandarización de Alertas SweetAlert2 Estilo Apple ── */
        .apple-popup {
            border-radius: 24px !important;
            font-family: 'Inter', -apple-system, sans-serif !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            padding: 24px !important;
            background: #ffffff !important;
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
            -webkit-font-smoothing: antialiased;
        }

        .apple-popup .swal2-title {
            font-family: var(--font-family) !important;
            font-size: 1.35rem !important;
            font-weight: 700 !important;
            color: #1d1d1f !important;
            letter-spacing: -0.02em !important;
            padding-top: 6px !important;
            margin-bottom: 8px !important;
        }

        .apple-popup .swal2-html-container {
            font-family: var(--font-family) !important;
            font-size: 0.94rem !important;
            font-weight: 400 !important;
            color: #6e6e73 !important;
            line-height: 1.5 !important;
            margin: 8px 12px 20px 12px !important;
        }

        .apple-popup .swal2-actions {
            display: flex !important;
            gap: 12px !important;
            margin-top: 18px !important;
            width: 100% !important;
            justify-content: center !important;
        }

        .btn-apple-confirm {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #0071e3 !important;
            color: #ffffff !important;
            font-family: var(--font-family) !important;
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            padding: 12px 26px !important;
            border-radius: 12px !important;
            border: none !important;
            cursor: pointer !important;
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.28) !important;
            transition: all 0.16s ease !important;
        }

        .btn-apple-confirm:hover {
            background: #0077ed !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 18px rgba(0, 113, 227, 0.38) !important;
        }

        .btn-apple-confirm:active {
            transform: scale(0.98) !important;
        }

        /* Botón de Cancelar (Gris suave) */
        .btn-apple-cancel {
            background-color: #f3f4f6 !important;
            color: #374151 !important;
            border-radius: 12px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            border: none !important;
            margin-right: 12px !important;
            cursor: pointer !important;
            transition: background-color 0.2s !important;
        }

        .btn-apple-cancel:hover {
            background-color: #e5e7eb !important;
        }

        /* Botón de Acción Destructiva (Rojo Apple) */
        .btn-apple-danger {
            background-color: #ef4444 !important;
            color: white !important;
            border-radius: 12px !important;
            padding: 12px 24px !important;
            font-weight: 600 !important;
            font-size: 1rem !important;
            border: none !important;
            cursor: pointer !important;
            transition: opacity 0.2s !important;
        }

        .btn-apple-danger:hover {
            opacity: 0.9 !important;
        }

        /* Notificación Toast estilo Apple */
        .apple-toast {
            border-radius: 16px !important;
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.12) !important;
            font-family: var(--font-family) !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
            padding: 12px 18px !important;
        }

        /* Evitar backdrops oscuros en las alertas pequeñas estilo Toast */
        body.swal2-shown:not(.swal2-no-backdrop) {
            overflow: auto !important; /* Permitir scroll */
        }

        /* Neutralizar fondo y desenfoque SOLO en el contenedor de Toasts */
        div:where(.swal2-container).swal2-top-end,
        .swal2-container.swal2-top-end,
        .swal2-container.swal2-top-end.swal2-backdrop-show {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            pointer-events: none !important; /* Deja pasar los clics hacia la interfaz que está debajo */
        }

        /* Restaurar la interactividad y diseño solo para la tarjeta de la alerta */
        div:where(.swal2-container) .swal2-popup.apple-toast,
        .swal2-popup.apple-toast {
            pointer-events: auto !important;
            backdrop-filter: blur(20px) !important; /* Solo la tarjeta tiene efecto glassmorphism */
            -webkit-backdrop-filter: blur(20px) !important;
            background: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        }

        /* ── Botón de Atrás Limpio en Topbar ── */
        .topbar-btn-atras {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #515154;
            font-size: 0.90rem;
            font-weight: 500;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 10px;
            transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease;
        }

        .topbar-btn-atras:hover {
            color: var(--primary-indigo, #3730a3);
            background: rgba(55, 48, 163, 0.06);
            transform: translateX(-2px);
        }

        .topbar-btn-atras i {
            font-size: 0.85rem;
        }

        /* ═════════════════════════════════════════════════════════════════ */
        /* RESPONSIVIDAD GLOBAL, HAMBURGUESA Y TABLAS (MEDIA QUERIES)       */
        /* ═════════════════════════════════════════════════════════════════ */

        /* Botón Hamburguesa Off-canvas (Oculto en Desktop) */
        .btn-toggle-sidebar {
            display: none;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.08);
            color: var(--text-heading);
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
            margin-right: 4px;
        }

        .btn-toggle-sidebar:hover {
            background: rgba(55, 48, 163, 0.08);
            color: var(--primary-indigo);
            border-color: rgba(55, 48, 163, 0.2);
        }

        /* Overlay semitransparente para cerrar el menú en móvil/tablet */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 95;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
            pointer-events: auto;
        }

        /* 1. Contenedor de tablas responsivo (Scroll horizontal suave) */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        .table-responsive table {
            min-width: 720px;
            width: 100%;
        }

        /* 2. Media Query para Tablets y Laptops Pequeñas (<= 1024px) */
        @media (max-width: 1024px) {
            .btn-toggle-sidebar {
                display: inline-flex;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 100;
                box-shadow: 0 0 50px rgba(15, 23, 42, 0.2);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .topbar {
                padding: 12px 20px !important;
            }

            .bento-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)) !important;
                gap: 16px !important;
            }

            .content-wrapper {
                padding: 20px !important;
            }
        }

        /* 3. Media Query para Smartphones (<= 768px) */
        @media (max-width: 768px) {
            .hide-on-mobile {
                display: none !important;
            }

            .page-title, .dashboard-main-title {
                font-size: 1.5rem !important;
            }

            .content-wrapper {
                padding: 16px 12px !important;
            }

            .topbar {
                padding: 10px 14px !important;
            }

            .user-pill-text {
                display: none !important;
            }

            .btn-topbar-logout span {
                display: none !important;
            }

            .btn-topbar-logout {
                padding: 8px 10px !important;
            }

            .dashboard-neo-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 14px !important;
            }

            .dashboard-neo-actions {
                width: 100%;
                display: flex;
                flex-direction: row;
                gap: 10px;
            }

            .dashboard-neo-actions button,
            .dashboard-neo-actions a {
                flex: 1;
                justify-content: center;
            }

            .bento-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }

            .table-neo-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px !important;
            }

            .table-neo-controls {
                width: 100% !important;
                justify-content: space-between !important;
            }

            .table-search-bar {
                flex-grow: 1 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Overlay oscuro para menú Off-canvas en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <!-- Ensamblador: 1. Sidebar Independiente -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Ensamblador: 2. Contenedor Principal -->
    <div class="main-wrapper">

        <!-- Ensamblador: 3. Topbar Independiente -->
        <?php require_once __DIR__ . '/topbar.php'; ?>
