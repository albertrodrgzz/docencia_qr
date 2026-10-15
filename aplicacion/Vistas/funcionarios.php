<?php
$page_title = 'Funcionarios';

/**
 * Directorio de Funcionarios — ISPEB Sistema Institucional
 * Ecosistema macOS / iOS con Layout Maestro y CSS Nativo
 *
 * @var array<int, array<string, mixed>>|null $datos
 */

$tituloVista       = 'Directorio de Funcionarios';
$mostrarBotonAtras = false;

require_once __DIR__ . '/layout/header.php';

// ── Paginación del Servidor (Server-Side Pagination) ──────────────────────
$limite = 10; // Registros por página

// Capturar página actual de forma segura (entero >= 1)
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina < 1) {
    $pagina = 1;
}

$inicio = ($pagina - 1) * $limite;

try {
    $pdo = \Configuracion\ConexionBD::getInstancia();

    // Consulta SQL 1: Obtener el total de registros
    $stmtCount = $pdo->query("SELECT COUNT(*) FROM funcionarios_registrados");
    $total_registros = (int) $stmtCount->fetchColumn();

    // Calcular el total de páginas
    $total_paginas = max(1, (int) ceil($total_registros / $limite));

    // Si la página excede el total, reajustar a la última
    if ($pagina > $total_paginas) {
        $pagina = $total_paginas;
        $inicio = ($pagina - 1) * $limite;
    }

    // Consulta SQL 2: Obtener registros del lote actual con LIMIT y OFFSET
    $stmt = $pdo->prepare("SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, registrado_por, fecha_registro 
                           FROM funcionarios_registrados 
                           ORDER BY id_funcionario DESC 
                           LIMIT :inicio, :limite");
    $stmt->bindValue(':inicio', (int) $inicio, PDO::PARAM_INT);
    $stmt->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
    $stmt->execute();
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) {
    $total_registros = count($funcionarios ?? []);
    $total_paginas   = max(1, (int) ceil($total_registros / $limite));
    $datos           = array_slice((array)($funcionarios ?? []), $inicio, $limite);
}

// Variables para el texto dinámico
$mostrando_desde = ($total_registros > 0) ? ($inicio + 1) : 0;
$mostrando_hasta = min($inicio + $limite, $total_registros);

if (!function_exists('obtenerIniciales')) {
    function obtenerIniciales(string $nombre): string {
        $partes = array_values(array_filter(explode(' ', trim($nombre))));
        if (empty($partes)) return 'FN';
        if (count($partes) === 1) return strtoupper(mb_substr($partes[0], 0, 2, 'UTF-8'));
        return strtoupper(mb_substr($partes[0], 0, 1, 'UTF-8') . mb_substr($partes[1], 0, 1, 'UTF-8'));
    }
}

if (!function_exists('obtenerColorAvatar')) {
    function obtenerColorAvatar(string $texto): array {
        $paletas = [
            ['bg' => '#e0e7ff', 'color' => '#3730a3'], // Índigo
            ['bg' => '#dbeafe', 'color' => '#1d4ed8'], // Azul
            ['bg' => '#dcfce7', 'color' => '#15803d'], // Esmeralda
            ['bg' => '#fef3c7', 'color' => '#b45309'], // Ámbar
            ['bg' => '#f3e8ff', 'color' => '#7e22ce'], // Púrpura
            ['bg' => '#ffe4e6', 'color' => '#be123c'], // Rosa
            ['bg' => '#ccfbf1', 'color' => '#0f766e'], // Turquesa
            ['bg' => '#ede9fe', 'color' => '#5b21b6'], // Violeta
        ];
        $indice = abs(crc32($texto)) % count($paletas);
        return $paletas[$indice];
    }
}
?>

<style>
    /* ── Estilos Específicos: Directorio de Funcionarios (Estilo Neo-Corporate) ── */

    /* ── Cabecera Hero Neo-Corporate ── */
    .funcionarios-neo-header {
        margin-bottom: 24px;
    }

    .funcionarios-subtitle-caps {
        font-size: 0.72rem;
        font-weight: 700;
        color: #2563eb;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 6px;
    }

    .funcionarios-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .funcionarios-main-title {
        font-size: 1.85rem;
        font-weight: 800;
        color: #1e1b4b;
        letter-spacing: -0.03em;
        margin: 0;
    }

    .trust-pills-group {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .trust-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.80rem;
        font-weight: 600;
        transition: transform 0.16s ease;
    }

    .trust-pill:hover {
        transform: translateY(-1px);
    }

    .pill-active-count {
        background: #dbeafe;
        color: #1e40af;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.10);
    }

    .pill-live-qr {
        background: #ffffff;
        color: #334155;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .live-dot-emerald {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        display: inline-block;
    }

    .pill-sha-verified {
        background: #ffffff;
        color: #334155;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .text-shield-blue {
        color: #2563eb;
    }

    /* Barra de herramientas interna (Toolbar) */
    .toolbar-funcionarios {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    /* Buscador estilo iOS con indicador ⌘K */
    .search-wrapper-ios {
        position: relative;
        width: 100%;
        max-width: 380px;
        display: flex;
        align-items: center;
    }

    .search-input-ios {
        width: 100%;
        height: 42px;
        padding: 0 54px 0 42px;
        border-radius: 12px;
        background: #f1f5f9;
        border: 1px solid rgba(0, 0, 0, 0.04);
        outline: none;
        font-family: inherit;
        font-size: 0.88rem;
        color: #1e293b;
        box-sizing: border-box;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .search-input-ios:focus {
        background: #ffffff;
        border-color: rgba(55, 48, 163, 0.4);
        box-shadow: 0 0 0 4px rgba(55, 48, 163, 0.08);
    }

    .search-input-ios::placeholder {
        color: #94a3b8;
    }

    .search-icon-svg {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.90rem;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .search-wrapper-ios:focus-within .search-icon-svg {
        color: #3730a3;
    }

    .search-kbd-tag {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 0.72rem;
        font-weight: 600;
        color: #94a3b8;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 6px;
        padding: 2px 6px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        pointer-events: none;
        user-select: none;
    }

    /* Grupo de botones principales a la derecha */
    .btn-actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    /* Botón Carga Masiva Excel (fondo blanco, borde sutil, texto/icono verde oscuro) */
    .btn-excel-green,
    .btn-apple-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 18px;
        border-radius: 12px;
        background: #ffffff;
        color: #047857;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        border: 1px solid rgba(16, 185, 129, 0.3);
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
    }

    .btn-excel-green:hover,
    .btn-apple-secondary:hover {
        background: #f0fdf4;
        border-color: #10b981;
        color: #065f46;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }

    .btn-excel-green i,
    .btn-apple-secondary i {
        color: #059669;
        font-size: 1rem;
    }

    /* Botón Registro Manual (fondo índigo brillante, texto blanco, sombra difuminada) */
    .btn-registro-indigo,
    .btn-apple-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 20px;
        border-radius: 12px;
        background: #3730a3;
        color: #ffffff;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        box-shadow: 0 8px 20px rgba(55, 48, 163, 0.25);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
    }

    .btn-registro-indigo:hover,
    .btn-apple-primary:hover {
        background: #4338ca;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(55, 48, 163, 0.35);
    }

    /* ── Tabla de Datos Hiper-Minimalista Estilo iOS ── */
    .table-container-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .table-minimal-ios {
        width: 100%;
        min-width: 800px;
        border-collapse: collapse;
        text-align: left;
    }

    .table-minimal-ios thead th {
        padding: 14px 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #f8fafc;
        white-space: nowrap;
    }

    .table-minimal-ios tbody tr {
        transition: background 0.18s ease;
    }

    .table-minimal-ios tbody tr:hover {
        background-color: #f8fafc;
    }

    .table-minimal-ios tbody td {
        padding: 14px 20px;
        font-size: 0.88rem;
        color: #1e293b;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
        white-space: nowrap;
    }

    .table-minimal-ios tbody tr:last-child td {
        border-bottom: none;
    }

    /* Píldora de Cédula: fondo #f3f4f6, border-radius: 6px */
    .badge-cedula-cell {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.82rem;
        font-weight: 600;
        background: #f3f4f6;
        color: #1f2937;
    }

    /* Avatares Dinámicos & Nombre */
    .user-avatar-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .cell-nombre {
        font-weight: 600;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    .cell-cargo {
        color: #475569;
        font-size: 0.88rem;
    }

    /* Píldora Departamento con punto de color */
    .depto-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 4px 10px;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .depto-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #6366f1;
        flex-shrink: 0;
    }

    .cell-depto {
        color: #334155;
        font-size: 0.82rem;
        font-weight: 500;
    }

    /* Acciones: Editar (gris oscuro, lápiz) y Ver QR (índigo, fondo #e0e7ff, sin bordes duros) */
    .actions-group-cell {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        justify-content: flex-end;
    }

    .btn-action-edit,
    .btn-editar-funcionario {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        background: transparent;
        color: #475569;
        font-family: inherit;
        font-size: 0.80rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.16s ease;
    }

    .btn-action-edit:hover,
    .btn-editar-funcionario:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #1e293b;
        box-shadow: none;
        border: none;
    }

    .btn-action-qr,
    .btn-ver-qr {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 8px;
        background: #e0e7ff;
        color: #3730a3;
        font-family: inherit;
        font-size: 0.80rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.16s ease;
    }

    .btn-action-qr:hover,
    .btn-ver-qr:hover {
        background: #c7d2fe;
        color: #312e81;
        transform: translateY(-1px);
        box-shadow: none;
    }

    /* ── Paginación Inferior Flex ── */
    .table-pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
        margin-top: 8px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info-text {
        font-size: 0.80rem;
        color: #64748b;
        font-weight: 500;
    }

    .pagination-controls-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-pagination-nav {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #ffffff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-pagination-nav:hover:not(:disabled):not(.disabled) {
        background: #f1f5f9;
        color: #3730a3;
        border-color: rgba(55, 48, 163, 0.2);
    }

    .btn-pagination-nav:disabled,
    .btn-pagination-nav.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }

    .page-number-active {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #3730a3;
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(55, 48, 163, 0.25);
    }

    /* ── Modales HTML Flotantes Estilo Apple (Ocultos) ── */
    .modal-overlay,
    .modal-backdrop-ios {
        position: fixed !important;
        top: 0 !important; left: 0 !important;
        width: 100vw !important; height: 100vh !important;
        z-index: 9999 !important;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease-out forwards;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-content,
    .modal-card-ios {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .modal-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e5e5ea;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-card-header h3 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1d1d1f;
    }

    .modal-close-btn {
        background: rgba(0, 0, 0, 0.05);
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-size: 1.1rem;
        cursor: pointer;
        color: #86868b;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease;
    }

    .modal-close-btn:hover {
        background: rgba(0, 0, 0, 0.10);
        color: #1d1d1f;
    }

    .modal-card-body {
        padding: 24px;
    }

    .form-group-row {
        margin-bottom: 16px;
    }

    .form-group-row label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #86868b;
        margin-bottom: 6px;
    }

    .form-input-clean {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        border-radius: 10px;
        border: 1px solid #e5e5ea;
        background: #f5f5f7;
        font-size: 0.90rem;
        font-family: inherit;
        color: #1d1d1f;
        outline: none;
        box-sizing: border-box;
        transition: all 0.18s ease;
    }

    .form-input-clean:focus {
        background: #ffffff;
        border-color: #0071e3;
        box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.12);
    }

    /* ── Zona de Subida Estilo Apple Drag & Drop (.upload-zone) ── */
    .upload-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 36px 20px;
        background: #f5f5f7;
        border: 2px dashed #d2d2d7;
        border-radius: 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
        margin-bottom: 20px;
    }

    .upload-zone:hover {
        background: #f0f0f4;
        border-color: #0071e3;
        transform: translateY(-1px);
    }

    .upload-zone .upload-icon {
        font-size: 2.8rem;
        color: #0071e3;
        margin-bottom: 12px;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .upload-zone:hover .upload-icon {
        transform: scale(1.08);
    }

    .upload-zone .upload-text-main {
        font-size: 0.90rem;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 4px;
    }

    .upload-zone .upload-text-hint {
        font-size: 0.78rem;
        color: #86868b;
    }

    #file-name {
        display: inline-block;
        margin-top: 10px;
        font-size: 0.84rem;
        font-weight: 600;
        color: #0071e3;
        word-break: break-all;
    }

    .upload-zone.file-selected {
        border: 2px solid #34c759;
        background: rgba(52, 199, 89, 0.05);
    }

    .upload-zone.file-selected .upload-icon {
        color: #34c759;
    }

    .upload-zone.file-selected #file-name {
        color: #248a3d;
    }

    .modal-card-footer {
        padding: 16px 24px 22px;
        border-top: 1px solid #e5e5ea;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-modal-cancel {
        height: 38px;
        padding: 0 16px;
        border-radius: 10px;
        background: transparent;
        border: 1px solid #e5e5ea;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 500;
        color: #86868b;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .btn-modal-cancel:hover {
        background: #f5f5f7;
        color: #1d1d1f;
    }

    .btn-modal-submit {
        height: 38px;
        padding: 0 20px;
        border-radius: 10px;
        background: #0071e3;
        color: #ffffff;
        border: none;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 113, 227, 0.25);
        transition: all 0.15s ease;
    }

    .btn-modal-submit:hover {
        background: #0077ed;
        box-shadow: 0 6px 14px rgba(0, 113, 227, 0.32);
    }

    /* Modal SweetAlert2 Apple */
    div:where(.swal2-container).swal2-center > .swal2-popup.apple-swal-modal {
        border-radius: 24px !important;
        padding: 28px !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.8) !important;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(25px) !important;
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Inter", sans-serif !important;
    }

    .apple-swal-btn {
        background: #0071e3 !important;
        border-radius: 12px !important;
        font-weight: 600 !important;
        font-size: 0.90rem !important;
        padding: 10px 24px !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25) !important;
        color: #fff !important;
        cursor: pointer !important;
    }

    .qr-frame {
        width: 190px;
        height: 190px;
        border-radius: 18px;
        padding: 10px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 10px auto 20px;
    }

    .qr-frame img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }

    .details-box {
        background: #f5f5f7;
        border-radius: 14px;
        padding: 14px 18px;
        text-align: left;
    }

    .details-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        font-size: 0.84rem;
        border-bottom: 1px solid #e5e5ea;
    }

    .details-row:last-child {
        border-bottom: none;
    }

    .details-row .label {
        color: #86868b;
        font-weight: 500;
    }

    .details-row .val {
        color: #1d1d1f;
        font-weight: 600;
        max-width: 240px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .details-row .val.mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <!-- ── Encabezado Neo-Corporate del Directorio ── -->
    <div class="funcionarios-neo-header">
        <div class="funcionarios-title-row">
            <h1 class="funcionarios-main-title">Directorio de Funcionarios</h1>
            <div class="trust-pills-group">
                <span class="trust-pill pill-active-count">
                    <?= count($datos) ?> Activos
                </span>
                <span class="trust-pill pill-live-qr">
                    <span class="live-dot-emerald" aria-hidden="true"></span>
                    <span>Validación QR En Vivo</span>
                </span>
                <span class="trust-pill pill-sha-verified">
                    <i class="fa-solid fa-shield-halved text-shield-blue" aria-hidden="true"></i>
                    <span>SHA-256 Verificado</span>
                </span>
            </div>
        </div>
    </div>

    <!-- ── Contenedor Principal (.content-card) ── -->
    <div class="content-card">

        <!-- ── Barra de Herramientas (Toolbar Interno) ── -->
        <div class="toolbar-funcionarios">
            
            <!-- Izquierda: Input de búsqueda estilizado con indicador ⌘K -->
            <div class="search-wrapper-ios">
                <i class="fa-solid fa-magnifying-glass search-icon-svg" aria-hidden="true"></i>
                <input 
                    type="text" 
                    id="inputBuscador" 
                    class="search-input-ios" 
                    placeholder="Buscar por cédula, nombre, cargo..."
                    aria-label="Buscar funcionarios"
                    autocomplete="off"
                >
                <kbd class="search-kbd-tag" title="Presiona ⌘K o Ctrl+K para buscar">⌘K</kbd>
            </div>

            <!-- Derecha: Botones Carga Masiva Excel y Registro Manual -->
            <div class="btn-actions-group">
                <!-- Carga Masiva Excel (Fondo blanco, borde sutil, texto/icono verde oscuro) -->
                <button type="button" class="btn-excel-green btn-apple-secondary" id="btnAbrirModalExcel" title="Carga masiva desde archivo Excel">
                    <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                    <span>Carga Masiva Excel</span>
                </button>

                <!-- Registro Manual (Fondo índigo brillante, texto blanco, sombra difuminada) -->
                <button type="button" class="btn-registro-indigo btn-apple-primary" id="btnAbrirModalRegistro" title="Registrar un nuevo funcionario institucional">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    <span>Registro Manual</span>
                </button>
            </div>

        </div>

        <!-- ── Tabla de Datos Hiper-Minimalista Edge-to-Edge ── -->
        <div class="table-container-responsive table-responsive">
            <table class="table-minimal-ios" id="tablaDatosFuncionarios">
                <thead>
                    <tr>
                        <th style="width: 140px;">Cédula</th>
                        <th>Nombre Completo</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th style="text-align: right; width: 180px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos)): ?>
                        <tr id="filaVacia">
                            <td colspan="5" style="text-align: center; padding: 48px 20px; color: #86868b;">
                                No hay funcionarios registrados en el directorio. Utiliza "Registro Manual" o "Carga Masiva Excel".
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos as $funcionario): 
                            $cedulaVal = htmlspecialchars((string)($funcionario['cedula'] ?? ''));
                            $nombreVal = htmlspecialchars((string)($funcionario['nombre_completo'] ?? '—'));
                            $cargoVal  = htmlspecialchars((string)($funcionario['cargo'] ?? '—'));
                            $deptoVal  = htmlspecialchars((string)($funcionario['departamento'] ?? '—'));
                            $iniciales = obtenerIniciales($nombreVal);
                            $avatarColor = obtenerColorAvatar($cedulaVal ?: $nombreVal);
                        ?>
                            <tr class="fila-funcionario" data-filtro="<?= strtolower($cedulaVal . ' ' . $nombreVal . ' ' . $cargoVal . ' ' . $deptoVal) ?>">
                                <td>
                                    <span class="badge-cedula-cell"><?= $cedulaVal ?: '—' ?></span>
                                </td>
                                <td>
                                    <div class="user-avatar-cell">
                                        <div class="avatar-circle-sm" style="background-color: <?= $avatarColor['bg'] ?>; color: <?= $avatarColor['color'] ?>;">
                                            <?= $iniciales ?>
                                        </div>
                                        <span class="cell-nombre"><?= $nombreVal ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="cell-cargo"><?= $cargoVal ?></span>
                                </td>
                                <td>
                                    <span class="depto-pill">
                                        <span class="depto-dot" aria-hidden="true"></span>
                                        <span class="cell-depto"><?= $deptoVal ?></span>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div class="actions-group-cell">
                                        <button 
                                            type="button" 
                                            class="btn-action-edit btn-editar-funcionario" 
                                            onclick="editarFuncionario('<?= $funcionario['cedula'] ?>')"
                                            title="Editar funcionario"
                                        >
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                            <span>Editar</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn-action-qr btn-ver-qr btn-action-view" 
                                            onclick="verFuncionario('<?= $funcionario['cedula'] ?>')"
                                            title="Ver credencial y código QR"
                                        >
                                            <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                                            <span>Ver QR</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div> <!-- /.table-container-responsive -->

        <!-- ── Paginación Inferior Dinámica ── -->
        <div class="table-pagination-footer">
            <div class="pagination-info-text">
                Mostrando <?= $mostrando_desde ?> a <?= $mostrando_hasta ?> de <?= $total_registros ?> funcionarios registrados • Total <?= $total_paginas ?> Página(s)
            </div>
            <div class="pagination-controls-group">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>" class="btn-pagination-nav" title="Página anterior" aria-label="Anterior">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <button type="button" class="btn-pagination-nav" disabled title="Página anterior" aria-label="Anterior">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>

                <span class="page-number-active" aria-current="page"><?= $pagina ?></span>

                <?php if ($pagina < $total_paginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>" class="btn-pagination-nav" title="Página siguiente" aria-label="Siguiente">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <button type="button" class="btn-pagination-nav" disabled title="Página siguiente" aria-label="Siguiente">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

    </div> <!-- /.content-card -->
</div> <!-- /.content-wrapper -->

<!-- ── Modal 1: Registro Manual (HTML Oculto) ── -->
<div class="modal-backdrop-ios modal-overlay" id="modalRegistroManual" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios modal-content">
        <div class="modal-card-header">
            <h3>Registro Manual de Funcionario</h3>
            <button type="button" class="modal-close-btn" id="btnCerrarModalReg" aria-label="Cerrar">&times;</button>
        </div>
        <form id="form-registro-manual" autocomplete="off">
            <div class="modal-card-body">
                <div class="form-group-row">
                    <label for="cedula">Cédula de Identidad</label>
                    <input type="text" id="cedula" name="cedula" class="form-input-clean" placeholder="Ej: V-12345678" required>
                </div>
                <div class="form-group-row">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-input-clean" placeholder="Ej: Dr. Alejandro Ramos" required>
                </div>
                <div class="form-group-row">
                    <label for="cargo">Cargo Institucional</label>
                    <input type="text" id="cargo" name="cargo" class="form-input-clean" placeholder="Ej: Coordinador Académico" required>
                </div>
                <div class="form-group-row">
                    <label for="departamento">Departamento / Unidad</label>
                    <input type="text" id="departamento" name="departamento" class="form-input-clean" placeholder="Ej: Dirección de Docencia" required>
                </div>
            </div>
            <div class="modal-card-footer">
                <button type="button" class="btn-modal-cancel" id="btnCancelarModalReg">Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="btnSubmitRegistro">Guardar y Generar QR</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal 2: Carga Masiva Excel (HTML Oculto) ── -->
<div class="modal-backdrop-ios modal-overlay" id="modalCargaExcel" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios modal-content">
        <div class="modal-card-header">
            <h3>Carga Masiva desde Excel</h3>
            <button type="button" class="modal-close-btn" id="btnCerrarModalExc" aria-label="Cerrar">&times;</button>
        </div>
        <form id="formCargaExcel" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-card-body">
                <p style="font-size:0.83rem;color:#86868b;margin:0 0 16px 0;line-height:1.45;">
                    El archivo <strong>.xlsx</strong> debe contener en su primera fila los encabezados: <em>Cédula, Nombre, Cargo, Departamento</em>.
                </p>
                
                <!-- Input Oculto -->
                <input type="file" name="archivo_excel" id="archivo_excel" accept=".xlsx" required style="display: none;">

                <!-- Zona de Subida Estilo Drag & Drop -->
                <label for="archivo_excel" class="upload-zone" id="dropZoneExcel">
                    <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                    <span class="upload-text-main">Haz clic para buscar tu archivo .xlsx</span>
                    <span class="upload-text-hint">o arrastra y suelta tu archivo aquí</span>
                    <span id="file-name"></span>
                </label>
            </div>
            <div class="modal-card-footer">
                <button type="button" class="btn-modal-cancel" id="btnCancelarModalExc">Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="btnSubmitExcel">Importar y Procesar</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Modal 3: Editar Funcionario (HTML Oculto Estilo Apple) ── -->
<div class="modal-backdrop-ios modal-overlay" id="modalEditarFuncionario" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios modal-content">
        <div class="modal-card-header">
            <h3>Editar Datos del Funcionario</h3>
            <button type="button" class="modal-close-btn" id="btnCerrarModalEditFunc" aria-label="Cerrar">&times;</button>
        </div>
        <form id="form-editar-funcionario" autocomplete="off">
            <input type="hidden" id="edit_cedula_original" name="cedula_original">
            <div class="modal-card-body">
                <div class="form-group-row">
                    <label for="edit_cedula">Cédula de Identidad</label>
                    <input type="text" id="edit_cedula" name="cedula" class="form-input-clean" placeholder="Ej: V-12345678" required>
                </div>
                <div class="form-group-row">
                    <label for="edit_nombre">Nombre Completo</label>
                    <input type="text" id="edit_nombre" name="nombre" class="form-input-clean" placeholder="Ej: Dr. Alejandro Ramos" required>
                </div>
                <div class="form-group-row">
                    <label for="edit_cargo">Cargo Institucional</label>
                    <input type="text" id="edit_cargo" name="cargo" class="form-input-clean" placeholder="Ej: Coordinador Académico" required>
                </div>
                <div class="form-group-row">
                    <label for="edit_departamento">Departamento / Unidad</label>
                    <input type="text" id="edit_departamento" name="departamento" class="form-input-clean" placeholder="Ej: Dirección de Docencia" required>
                </div>
            </div>
            <div class="modal-card-footer">
                <button type="button" class="btn-modal-cancel" id="btnCancelarModalEditFunc">Cancelar</button>
                <button type="submit" class="btn-modal-submit" id="btnSubmitEditFunc">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ── JavaScript de la Vista ── -->
<script>
/**
 * Abre el modal de edición y carga los datos del funcionario desde la API.
 * Accesible globalmente para cualquier rol.
 * @param {string} cedula
 */
async function editarFuncionario(cedula) {
    if (!cedula) return;

    try {
        const response = await fetch(BASE_URL + '/api/funcionarios/obtener?cedula=' + encodeURIComponent(cedula), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (!response.ok || data.estado !== 'ok' || !data.funcionario) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'error',
                    title: data.mensaje || 'No se pudo obtener el funcionario.'
                });
            }
            return;
        }

        const func = data.funcionario;
        const inputOrig = document.getElementById('edit_cedula_original');
        const inputCed  = document.getElementById('edit_cedula');
        const inputNom  = document.getElementById('edit_nombre');
        const inputCar  = document.getElementById('edit_cargo');
        const inputDep  = document.getElementById('edit_departamento');

        if (inputOrig) inputOrig.value = func.cedula || '';
        if (inputCed)  inputCed.value  = func.cedula || '';
        if (inputNom)  inputNom.value  = func.nombre_completo || '';
        if (inputCar)  inputCar.value  = func.cargo || '';
        if (inputDep)  inputDep.value  = func.departamento || '';

        const modalEdit = document.getElementById('modalEditarFuncionario');
        if (modalEdit) {
            modalEdit.style.display = 'flex';
            modalEdit.setAttribute('aria-hidden', 'false');
            inputNom?.focus();
        }
    } catch (err) {
        console.error('[editarFuncionario Error]', err);
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'error',
                title: 'Error al consultar datos del funcionario.'
            });
        }
    }
}

/**
 * Consulta la API y despliega el modal SweetAlert2 con el QR en Base64 y datos del funcionario.
 * @param {string} cedula
 */
async function verFuncionario(cedula) {
    if (!cedula) return;

    Swal.fire({
        title: 'Cargando...',
        text: 'Obteniendo datos y código QR institucional.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        customClass: { popup: 'apple-swal-modal' },
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch(BASE_URL + '/api/funcionario?cedula=' + encodeURIComponent(cedula), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();
        Swal.close();

        if (!data.exito) {
            Swal.fire({
                title: 'No encontrado',
                text: data.mensaje || 'No se pudieron recuperar los datos de la cédula ' + cedula,
                icon: 'error',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                buttonsStyling: false
            });
            return;
        }

        const nombre = data.nombre || (data.funcionario ? data.funcionario.nombre_completo : '—');
        const cargo = data.cargo || (data.funcionario ? data.funcionario.cargo : '—');
        const depto = data.departamento || (data.funcionario ? data.funcionario.departamento : '—');
        const cedulaVal = data.cedula || cedula;
        const qrSrc = data.qr_base64;

        Swal.fire({
            title: 'Credencial Institucional',
            html: `
                <div style="display:flex;justify-content:center;">
                    <div class="qr-frame">
                        ${qrSrc 
                            ? `<img src="${qrSrc}" alt="QR de ${nombre}">` 
                            : `<div style="width:170px;height:170px;display:flex;align-items:center;justify-content:center;color:#86868b;font-size:0.8rem;">Sin QR disponible</div>`
                        }
                    </div>
                </div>

                <div class="details-box">
                    <div class="details-row">
                        <span class="label">Nombre</span>
                        <span class="val" title="${nombre}">${nombre}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Cédula</span>
                        <span class="val mono">${cedulaVal}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Cargo</span>
                        <span class="val" title="${cargo}">${cargo}</span>
                    </div>
                    <div class="details-row">
                        <span class="label">Departamento</span>
                        <span class="val" title="${depto}">${depto}</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'apple-swal-modal',
                confirmButton: 'apple-swal-btn'
            },
            buttonsStyling: false
        });

    } catch (error) {
        console.error('[ISPEB]', error);
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No fue posible comunicar con el servidor.',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
            buttonsStyling: false
        });
    }
}

// ── Controladores de Modales y Búsqueda en Vivo ──
(function () {
    'use strict';

    const modalReg = document.getElementById('modalRegistroManual');
    const modalExc = document.getElementById('modalCargaExcel');
    const inputBuscar = document.getElementById('inputBuscador');
    const filas = document.querySelectorAll('.fila-funcionario');

    // 1. Filtrado en tiempo real en la tabla
    inputBuscar?.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        filas.forEach(fila => {
            const contenido = fila.getAttribute('data-filtro') || '';
            fila.style.display = contenido.includes(query) ? '' : 'none';
        });
    });

    // 2. Control de Apertura de Modales
    document.getElementById('btnAbrirModalRegistro')?.addEventListener('click', () => {
        document.getElementById('form-registro-manual')?.reset();
        modalReg.style.display = 'flex';
        modalReg.setAttribute('aria-hidden', 'false');
        document.getElementById('cedula')?.focus();
    });

    document.getElementById('btnAbrirModalExcel')?.addEventListener('click', () => {
        document.getElementById('formCargaExcel')?.reset();
        modalExc.style.display = 'flex';
        modalExc.setAttribute('aria-hidden', 'false');
    });

    // 3. Control de Cierre de Modales con Transición Fluida Apple (.fade-out)
    const modalEdit = document.getElementById('modalEditarFuncionario');

    function cerrarModales() {
        const modales = [modalReg, modalExc, modalEdit].filter(m => m && m.style.display !== 'none');
        if (modales.length === 0) return;

        modales.forEach(m => m.classList.add('fade-out'));

        setTimeout(() => {
            modales.forEach(m => {
                m.style.display = 'none';
                m.setAttribute('aria-hidden', 'true');
                m.classList.remove('fade-out');
            });
        }, 240);
    }

    document.getElementById('btnCerrarModalReg')?.addEventListener('click', cerrarModales);
    document.getElementById('btnCancelarModalReg')?.addEventListener('click', cerrarModales);
    document.getElementById('btnCerrarModalExc')?.addEventListener('click', cerrarModales);
    document.getElementById('btnCancelarModalExc')?.addEventListener('click', cerrarModales);
    document.getElementById('btnCerrarModalEditFunc')?.addEventListener('click', cerrarModales);
    document.getElementById('btnCancelarModalEditFunc')?.addEventListener('click', cerrarModales);

    [modalReg, modalExc, modalEdit].forEach(m => {
        m?.addEventListener('click', (e) => {
            if (e.target === m) cerrarModales();
        });
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') cerrarModales();
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            inputBuscar?.focus();
            inputBuscar?.select();
        }
    });

    // Función auxiliar para escapar texto HTML en inserciones dinámicas
    function escaparTextoHtml(texto) {
        if (!texto) return '';
        const d = document.createElement('div');
        d.textContent = String(texto);
        return d.innerHTML;
    }

    // Función auxiliar para calcular iniciales en frontend
    function obtenerInicialesJs(nombre) {
        if (!nombre) return 'FN';
        const partes = nombre.trim().split(/\s+/).filter(Boolean);
        if (partes.length === 0) return 'FN';
        if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
        return (partes[0].substring(0, 1) + partes[1].substring(0, 1)).toUpperCase();
    }

    // 4. Envío AJAX Registro Manual en Tiempo Real (Magia Sin Recarga)
    const formRegistroManual = document.getElementById('form-registro-manual');

    formRegistroManual?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = this;
        const btnSubmit = document.getElementById('btnSubmitRegistro');
        const cedula = document.getElementById('cedula')?.value.trim() || '';
        const nombre = document.getElementById('nombre')?.value.trim() || '';
        const cargo  = document.getElementById('cargo')?.value.trim() || '';
        const depto  = document.getElementById('departamento')?.value.trim() || '';

        if (!cedula || !nombre || !cargo || !depto) {
            Toast.fire({
                icon: 'warning',
                title: 'Completa todos los campos obligatorios'
            });
            return;
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';
        }

        try {
            const formData = new FormData(form);

            const res = await fetch(BASE_URL + '/funcionarios/guardar', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();

            if (data.exito) {
                // Alerta Toast Apple instantánea
                Toast.fire({
                    icon: 'success',
                    title: 'Registrado correctamente'
                });

                // Cierra el modal automáticamente y limpia el formulario
                cerrarModales();
                form.reset();

                // Magia en Tiempo Real: Inyectar la nueva fila con animación al <tbody>
                const tbody = document.querySelector('#tablaDatosFuncionarios tbody');
                if (tbody) {
                    const filaVacia = document.getElementById('filaVacia');
                    if (filaVacia) {
                        filaVacia.remove();
                    }

                    const iniciales = obtenerInicialesJs(nombre);
                    const nuevaFila = document.createElement('tr');
                    nuevaFila.className = 'fila-funcionario row-highlight-new';
                    nuevaFila.setAttribute('data-filtro', `${cedula} ${nombre} ${cargo} ${depto}`.toLowerCase());
                    nuevaFila.innerHTML = `
                        <td>
                            <span class="badge-cedula-cell">${escaparTextoHtml(cedula)}</span>
                        </td>
                        <td>
                            <div class="user-avatar-cell">
                                <div class="avatar-circle-sm" style="background-color: #e0e7ff; color: #3730a3;">
                                    ${iniciales}
                                </div>
                                <span class="cell-nombre">${escaparTextoHtml(nombre)}</span>
                            </div>
                        </td>
                        <td>
                            <span class="cell-cargo">${escaparTextoHtml(cargo)}</span>
                        </td>
                        <td>
                            <span class="depto-pill">
                                <span class="depto-dot" aria-hidden="true"></span>
                                <span class="cell-depto">${escaparTextoHtml(depto)}</span>
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div class="actions-group-cell">
                                <button type="button" class="btn-action-edit btn-editar-funcionario" onclick="editarFuncionario('${escaparTextoHtml(cedula)}')" title="Editar funcionario">
                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                    <span>Editar</span>
                                </button>
                                <button type="button" class="btn-action-qr btn-ver-qr btn-action-view" onclick="verFuncionario('${escaparTextoHtml(cedula)}')" title="Ver datos y código QR">
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                                    <span>Ver QR</span>
                                </button>
                            </div>
                        </td>
                    `;

                    tbody.prepend(nuevaFila);
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.mensaje || data.error || 'No fue posible registrar al funcionario.'
                });
            }
        } catch (err) {
            console.error('[ISPEB]', err);
            Toast.fire({
                icon: 'error',
                title: 'Error de comunicación con el servidor.'
            });
        } finally {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Guardar y Generar QR';
            }
        }
    });

    // 5. Dinámica de la Zona de Subida Excel
    const inputExcel   = document.getElementById('archivo_excel');
    const fileNameSpan = document.getElementById('file-name');
    const uploadZone   = document.querySelector('.upload-zone');

    inputExcel?.addEventListener('change', function (e) {
        if (e.target.files && e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            if (fileNameSpan) fileNameSpan.textContent = 'Archivo seleccionado: ' + fileName;
            uploadZone?.classList.add('file-selected');
        } else {
            if (fileNameSpan) fileNameSpan.textContent = '';
            uploadZone?.classList.remove('file-selected');
        }
    });

    document.getElementById('btnAbrirModalExcel')?.addEventListener('click', () => {
        if (fileNameSpan) fileNameSpan.textContent = '';
        uploadZone?.classList.remove('file-selected');
    });

    // 6. Envío AJAX Carga Masiva Excel
    document.getElementById('formCargaExcel')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const fileInput = document.getElementById('archivo_excel') || document.getElementById('documento_excel');
        if (!fileInput || !fileInput.files.length) {
            Swal.fire({
                title: 'Archivo requerido',
                text: 'Por favor selecciona un archivo .xlsx antes de procesar.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                buttonsStyling: false
            });
            return;
        }

        Swal.fire({
            title: 'Procesando archivo…',
            text: 'Importando funcionarios y generando códigos QR en lote.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
            customClass: { popup: 'apple-swal-modal' }
        });

        try {
            const formData = new FormData(this);
            const res = await fetch(BASE_URL + '/registro-excel', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.estado === 'success' || data.exito) {
                cerrarModales();
                await Swal.fire({
                    title: '¡Carga Completada!',
                    text: data.mensaje || 'Se han importado los funcionarios y generado sus códigos QR.',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                    buttonsStyling: false
                });
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error en la Importación',
                    text: data.mensaje || 'Ocurrió un error al procesar el archivo Excel.',
                    icon: 'error',
                    confirmButtonText: 'Revisar',
                    customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                    buttonsStyling: false
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                title: 'Error de Red',
                text: 'No se pudo comunicar con el servidor durante la subida.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
                customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                buttonsStyling: false
            });
        }
    });

    // ── Interceptar submit de Edición de Funcionario (AJAX reactivo) ──
    const formEditarFuncionario = document.getElementById('form-editar-funcionario');
    formEditarFuncionario?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const btnSubmit = document.getElementById('btnSubmitEditFunc');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';
        }

        const formData = new FormData(this);

        try {
            const response = await fetch(BASE_URL + '/api/funcionarios/actualizar', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Guardar Cambios';
            }

            if (!response.ok || data.estado !== 'ok') {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: data.mensaje || 'Error al actualizar funcionario.'
                    });
                }
                return;
            }

            cerrarModales();

            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: data.mensaje || 'Funcionario actualizado con éxito.'
                });
            }

            // Actualizar la fila en la tabla reactivamente sin recargar
            const cedulaOriginal = String(formData.get('cedula_original') || '').trim();
            const cedulaNueva    = String(data.funcionario?.cedula || formData.get('cedula') || '').trim();
            const nombreNuevo    = String(data.funcionario?.nombre_completo || formData.get('nombre') || '').trim();
            const cargoNuevo     = String(data.funcionario?.cargo || formData.get('cargo') || '').trim();
            const deptoNuevo     = String(data.funcionario?.departamento || formData.get('departamento') || '').trim();

            const filas = document.querySelectorAll('.fila-funcionario');
            filas.forEach(fila => {
                const btnEdit = fila.querySelector('.btn-editar-funcionario');
                if (btnEdit && btnEdit.getAttribute('onclick')?.includes(cedulaOriginal)) {
                    const cellCedula = fila.querySelector('.badge-cedula-cell');
                    const cellNombre = fila.querySelector('.cell-nombre');
                    const cellCargo  = fila.querySelector('.cell-cargo');
                    const cellDepto  = fila.querySelector('.cell-depto');
                    const btnVerQr   = fila.querySelector('.btn-ver-qr, .btn-action-view');

                    if (cellCedula) cellCedula.textContent = cedulaNueva;
                    if (cellNombre) cellNombre.textContent = nombreNuevo;
                    const avatarEl = fila.querySelector('.avatar-circle-sm');
                    if (avatarEl) avatarEl.textContent = obtenerInicialesJs(nombreNuevo);
                    if (cellCargo)  cellCargo.textContent  = cargoNuevo;
                    if (cellDepto)  cellDepto.textContent  = deptoNuevo;

                    btnEdit.setAttribute('onclick', `editarFuncionario('${cedulaNueva}')`);
                    if (btnVerQr) btnVerQr.setAttribute('onclick', `verFuncionario('${cedulaNueva}')`);
                    fila.setAttribute('data-filtro', `${cedulaNueva} ${nombreNuevo} ${cargoNuevo} ${deptoNuevo}`.toLowerCase());

                    // Micro-interacción: Destello suave de fila actualizada
                    fila.style.transition = 'background-color 0.4s ease';
                    fila.style.backgroundColor = 'rgba(52, 199, 89, 0.15)';
                    setTimeout(() => { fila.style.backgroundColor = ''; }, 1200);
                }
            });

        } catch (err) {
            console.error('[actualizarFuncionario Error]', err);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Guardar Cambios';
            }
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de red al actualizar.'
                });
            }
        }
    });

})();
</script>
