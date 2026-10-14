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
    <meta name="description" content="Sistema Institucional de Certificaciones y Validación QR — ISPEB">
    <title><?= $tituloPagina ?> — ISPEB</title>

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

        /* ── Variables de Diseño Apple macOS / iPadOS ── */
        :root {
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", sans-serif;
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-blue-tint: rgba(0, 113, 227, 0.08);
            --apple-purple: #af52de;
            --apple-purple-tint: rgba(175, 82, 222, 0.10);
            --apple-green: #34c759;
            --apple-green-tint: rgba(52, 199, 89, 0.12);
            --bg-page: #f5f5f7;
            --bg-card: #ffffff;
            --text-heading: #1d1d1f;
            --text-secondary: #86868b;
            --border-subtle: rgba(0, 0, 0, 0.06);
            --sidebar-width: 260px;
            --radius-card: 24px;
            --radius-control: 12px;
        }

        /* ── Reseteo y Base Tipográfica ── */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f7;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #1d1d1f;
            letter-spacing: -0.015em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
        }

        /* ── 1. Sidebar Fijo a la Izquierda (260px) ── */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(255, 255, 255, 0.70);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 0, 0, 0.07);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            z-index: 90;
            box-sizing: border-box;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px 24px 12px;
            text-decoration: none;
            color: var(--text-heading);
            border-bottom: 1px solid var(--border-subtle);
            margin-bottom: 20px;
        }

        .brand-icon-sq {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--apple-blue) 0%, #0051c6 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-weight: 700;
            font-size: 1.12rem;
            letter-spacing: -0.02em;
            line-height: 1.15;
        }

        .brand-desc {
            font-size: 0.72rem;
            color: var(--text-secondary);
            font-weight: 500;
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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 1.05rem;
            font-weight: 500;
            letter-spacing: -0.015em;
            color: #515154;
            padding: 12px 20px;
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); /* Transición Apple */
            /* Animación de cascada al cargar el menú */
            animation: slideInRight 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            display: flex;
            align-items: center;
            text-decoration: none;
            box-sizing: border-box;
        }

        .sidebar a:hover {
            background: rgba(0, 0, 0, 0.04);
            transform: translateX(6px); /* Efecto de deslizamiento sutil al pasar el mouse */
            color: #1d1d1f;
        }

        .sidebar a.active {
            background: #0071e3;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(0, 113, 227, 0.3); /* Resplandor azul en el botón activo */
            transform: none; /* Que el botón activo no se mueva al hacer hover */
        }

        .sidebar a.active:hover {
            transform: none;
            background: #0071e3;
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
            width: 28px;
            font-size: 1.2rem;
            text-align: center;
            margin-right: 12px;
            color: #86868b;
            transition: color 0.3s ease;
            display: inline-block;
            flex-shrink: 0;
        }

        .sidebar a.active i {
            color: #ffffff !important;
        }

        /* ── 2. Contenedor Principal (.main-wrapper) ── */
        .main-wrapper {
            margin-left: 260px;
            min-height: 100vh;
        }

        /* ── 3. Topbar (.topbar sticky con blur y ancho 100%) ── */
        .topbar {
            position: sticky;
            top: 0;
            width: 100%;
            box-sizing: border-box;
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            z-index: 90;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-left .page-title,
        .topbar-left h1,
        .page-title {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.045em;
            color: #1d1d1f;
            margin: 0;
            line-height: 1.15;
            /* El secreto: Una sombra de texto difuminada que lo separa del cristal de fondo */
            text-shadow: 0px 4px 15px rgba(0, 0, 0, 0.08);
        }

        /* ── 4. Contenedor de Contenido (.content-wrapper) con padding 30px ── */
        .content-wrapper {
            padding: 30px;
            box-sizing: border-box;
        }

        /* Botón Atrás circular estilizado */
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
            gap: 16px;
        }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 6px;
            background: rgba(255, 255, 255, 0.70);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 20px;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
            transition: all 0.16s ease;
        }

        .user-profile-badge:hover {
            background-color: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
        }

        .user-avatar-initials {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--apple-blue);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.76rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
        }

        .user-text-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .user-text-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-heading);
        }

        .user-text-role {
            font-size: 0.70rem;
            color: var(--text-secondary);
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

        /* Aplicar animación de entrada al contenido principal */
        .content-card, .content-wrapper {
            animation: fadeUpSmooth 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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
            padding: 28px 32px !important;
            background: #ffffff !important;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.16), 0 4px 16px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid rgba(0, 0, 0, 0.06) !important;
            font-family: var(--font-family) !important;
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

        .btn-apple-cancel {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #f5f5f7 !important;
            color: #1d1d1f !important;
            font-family: var(--font-family) !important;
            font-size: 0.92rem !important;
            font-weight: 500 !important;
            padding: 12px 24px !important;
            border-radius: 12px !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            cursor: pointer !important;
            transition: all 0.16s ease !important;
        }

        .btn-apple-cancel:hover {
            background: #e8e8ed !important;
            color: #000000 !important;
        }

        .btn-apple-cancel:active {
            transform: scale(0.98) !important;
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
    </style>
</head>
<body>

    <!-- Ensamblador: 1. Sidebar Independiente -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Ensamblador: 2. Contenedor Principal -->
    <div class="main-wrapper">

        <!-- Ensamblador: 3. Topbar Independiente -->
        <?php require_once __DIR__ . '/topbar.php'; ?>
