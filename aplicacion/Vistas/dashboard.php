<?php
/**
 * Vista de Dashboard — Sistema Institucional de Certificaciones ISPEB
 * Implementación modular con plantillas maestras (Layout Header + Footer)
 *
 * @var array{
 *     total_funcionarios: int,
 *     registrados_hoy: int,
 *     ultimos_funcionarios: array<int, array<string, mixed>>
 * } $datos
 */

$tituloVista       = 'Panel de Control';
$mostrarBotonAtras = false; // No hay botón atrás en la vista raíz del dashboard
$menuActivo        = 'dashboard';

$totalFuncionarios = (int) ($datos['total_funcionarios'] ?? 0);
$registradosHoy    = (int) ($datos['registrados_hoy'] ?? 0);
$ultimosRegistros  = (array) ($datos['ultimos_funcionarios'] ?? []);

require_once __DIR__ . '/layout/header.php';
?>

<style>
    /* ── 1. Encabezado Neo-Corporate ── */
    .dashboard-neo-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .dashboard-neo-titles {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .dashboard-main-title {
        font-family: var(--font-family);
        font-size: 2.15rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: var(--text-heading);
        margin: 0;
        line-height: 1.15;
    }

    .dashboard-header-desc {
        color: var(--text-secondary);
        font-size: 0.90rem;
        margin: 0;
        font-weight: 400;
    }

    /* Botones Superiores */
    .dashboard-neo-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 4px;
    }

    /* Carga Excel: fondo blanco, detalles verdes */
    .btn-action-excel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 18px;
        background: #ffffff;
        color: #059669; /* Detalles verdes */
        border: 1px solid rgba(5, 150, 105, 0.28);
        border-radius: var(--radius-control);
        font-family: inherit;
        font-size: 0.86rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.08);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-action-excel:hover {
        background: #f0fdf4;
        border-color: #059669;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(5, 150, 105, 0.16);
    }

    .btn-action-excel i {
        font-size: 1.05rem;
        color: #10b981;
    }

    /* Nuevo Registro: fondo índigo, texto blanco */
    .btn-action-indigo {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 20px;
        background: var(--primary-indigo, #3730a3);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-control);
        font-family: inherit;
        font-size: 0.86rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(55, 48, 163, 0.28);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-action-indigo:hover {
        background: var(--primary-indigo-hover, #312e81);
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(55, 48, 163, 0.35);
    }

    .btn-action-indigo i {
        font-size: 0.90rem;
    }

    /* ── 2. Bento Grid y Tarjetas de Métricas (Widgets) ── */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .bento-card {
        position: relative;
        overflow: hidden;
        background: #ffffff;
        border-radius: 20px;
        padding: 24px 24px 22px 24px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); /* Sombra con mayor profundidad */
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease, border-color 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 154px;
    }

    .bento-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 42px rgba(0, 0, 0, 0.12);
        border-color: rgba(55, 48, 163, 0.18);
    }

    .bento-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .bento-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 1.15rem;
    }

    .bento-card-icon.indigo {
        background: linear-gradient(135deg, #4338ca, #3730a3);
        box-shadow: 0 4px 12px rgba(55, 48, 163, 0.22);
    }

    .bento-card-icon.emerald {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.22);
    }

    .bento-card-icon.purple {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9);
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.22);
    }

    /* Etiqueta tipo píldora en la esquina superior derecha */
    .bento-pill-tag {
        font-size: 0.70rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 4px 12px;
        border-radius: var(--radius-pill);
    }

    .bento-pill-tag.tag-indigo {
        background: rgba(55, 48, 163, 0.08);
        color: var(--primary-indigo, #3730a3);
        border: 1px solid rgba(55, 48, 163, 0.16);
    }

    .bento-pill-tag.tag-emerald {
        background: rgba(16, 185, 129, 0.10);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.20);
    }

    .bento-pill-tag.tag-purple {
        background: rgba(139, 92, 246, 0.10);
        color: #6d28d9;
        border: 1px solid rgba(139, 92, 246, 0.20);
    }

    /* Número principal y tendencia verde */
    .bento-card-values {
        display: flex;
        align-items: baseline;
        gap: 12px;
        z-index: 2;
    }

    .bento-main-number {
        font-size: 2.35rem;
        font-weight: 800;
        letter-spacing: -0.035em;
        color: var(--text-heading);
        line-height: 1.1;
    }

    .bento-trend-green {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #15803d; /* Verde fuerte */
        background: #dcfce7; /* Fondo pastel verde */
        padding: 3px 8px;
        border-radius: var(--radius-pill);
        border: 1px solid rgba(22, 163, 74, 0.22);
    }

    .bento-card-label {
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--text-secondary);
        margin-top: 5px;
        z-index: 2;
    }

    /* Ícono grande translúcido como marca de agua en el fondo derecho */
    .bento-watermark-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 6rem;
        opacity: 0.15;
        pointer-events: none;
        user-select: none;
        z-index: 1;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
    }

    .bento-card:hover .bento-watermark-icon {
        transform: scale(1.08) rotate(-4deg);
        opacity: 0.22;
    }

    /* Colores correspondientes a cada tarjeta */
    .watermark-indigo,
    .bento-card:nth-child(1) .bento-watermark-icon {
        color: #3730a3 !important;
    }

    .watermark-emerald,
    .bento-card:nth-child(2) .bento-watermark-icon {
        color: #10b981 !important;
    }

    .watermark-purple,
    .bento-card:nth-child(3) .bento-watermark-icon {
        color: #8b5cf6 !important;
    }

    /* ── 3. Cabecera de la Tabla Neo-Corporate ── */
    .table-neo-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .table-neo-title-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Título que inicia con un punto azul e incluye una píldora indicadora */
    .table-dot-blue {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
        display: inline-block;
        flex-shrink: 0;
    }

    .table-neo-title-text {
        font-size: 1.18rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: var(--text-heading);
        margin: 0;
    }

    .table-indicator-pill {
        display: inline-flex;
        align-items: center;
        font-size: 0.74rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid rgba(148, 163, 184, 0.24);
        padding: 3px 11px;
        border-radius: var(--radius-pill);
    }

    /* Controles derechos: Barra de búsqueda y botón de refresco */
    .table-neo-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-search-bar {
        position: relative;
        display: flex;
        align-items: center;
    }

    .table-search-bar i {
        position: absolute;
        left: 14px;
        font-size: 0.82rem;
        color: #94a3b8;
        pointer-events: none;
    }

    .table-search-input {
        height: 38px;
        padding: 0 16px 0 36px;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: var(--radius-pill);
        font-family: inherit;
        font-size: 0.83rem;
        color: var(--text-heading);
        outline: none;
        width: 230px;
        transition: all 0.2s ease;
    }

    .table-search-input:focus {
        background: #ffffff;
        border-color: rgba(55, 48, 163, 0.35);
        box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.08);
        width: 270px;
    }

    .table-btn-refresh {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        transition: all 0.25s ease;
        outline: none;
    }

    .table-btn-refresh:hover {
        background: #f8fafc;
        color: var(--primary-indigo, #3730a3);
        border-color: rgba(55, 48, 163, 0.25);
        transform: rotate(90deg);
    }

    /* ── 4. Estilo de Celdas y Contenedor de Tabla con Profundidad ── */
    .content-card {
        background: var(--bg-card);
        border-radius: var(--radius-card);
        padding: 30px 32px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08) !important; /* Sombra más profunda para separarla del fondo */
        border: 1px solid rgba(15, 23, 42, 0.06) !important;
        margin-bottom: 30px;
        box-sizing: border-box;
    }

    .clean-table-container {
        width: 100%;
        overflow-x: auto;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, 0.06);
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
    }

    .clean-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.90rem;
        text-align: left;
    }

    .clean-table th {
        padding: 14px 20px;
        font-size: 0.74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #86868b;
        background-color: #fafbfc;
        border-bottom: 1px solid rgba(15, 23, 42, 0.05);
        white-space: nowrap;
    }

    .clean-table tbody tr {
        transition: background 0.18s ease;
    }

    .clean-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .clean-table td {
        padding: 15px 20px;
        color: #0f172a;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(15, 23, 42, 0.04);
        vertical-align: middle;
    }

    .clean-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Píldora Cédula: fondo pastel y texto fuerte */
    .cell-pill-cedula {
        display: inline-flex;
        align-items: center;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.81rem;
        font-weight: 700;
        color: #1e293b; /* Texto fuerte */
        background: #f1f5f9; /* Fondo pastel gris perla */
        border: 1px solid rgba(148, 163, 184, 0.25);
        padding: 4px 12px;
        border-radius: var(--radius-pill);
        letter-spacing: 0.02em;
    }

    .cell-title {
        font-weight: 600;
        color: var(--text-heading);
        display: block;
    }

    .cell-subtitle {
        font-size: 0.76rem;
        color: var(--text-secondary);
        margin-top: 1px;
        display: block;
    }

    /* Píldora Estado "QR Emitido" (Verde pastel + texto fuerte) */
    .cell-pill-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.76rem;
        font-weight: 700;
        color: #15803d; /* Verde fuerte */
        background: #dcfce7; /* Fondo pastel verde */
        border: 1px solid rgba(34, 197, 94, 0.25);
        padding: 4px 12px;
        border-radius: var(--radius-pill);
        letter-spacing: -0.01em;
    }

    .cell-status-dot-green {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
    }

    /* Botón "Ver QR" (Azul claro pastel + texto fuerte) */
    .btn-pill-view-qr {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 15px;
        border-radius: var(--radius-pill);
        font-family: inherit;
        font-size: 0.79rem;
        font-weight: 700;
        background: #e0f2fe; /* Azul claro pastel */
        color: #0369a1; /* Azul fuerte */
        border: 1px solid rgba(2, 132, 199, 0.22);
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 2px 6px rgba(2, 132, 199, 0.06);
    }

    .btn-pill-view-qr:hover {
        background: #bae6fd;
        color: #075985;
        border-color: rgba(2, 132, 199, 0.35);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.18);
    }

    .btn-pill-view-qr i {
        font-size: 0.85rem;
    }

    .empty-message {
        padding: 40px 20px;
        text-align: center;
        color: var(--text-secondary);
    }

    /* ── Estilos SweetAlert2 QR Modal ── */
    .swal2-popup.apple-swal-modal {
        border-radius: 24px !important;
        padding: 28px 24px 22px !important;
        background: rgba(255, 255, 255, 0.96) !important;
        backdrop-filter: blur(28px) saturate(180%) !important;
        -webkit-backdrop-filter: blur(28px) saturate(180%) !important;
        box-shadow: 0 28px 65px rgba(0, 0, 0, 0.16) !important;
        font-family: var(--font-family) !important;
        max-width: 440px !important;
    }

    .swal2-popup.apple-swal-modal .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 700 !important;
        color: var(--text-heading) !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 16px !important;
    }

    .swal2-confirm.apple-swal-btn {
        background-color: var(--apple-blue) !important;
        color: #ffffff !important;
        border-radius: 12px !important;
        padding: 11px 28px !important;
        font-size: 0.90rem !important;
        font-weight: 600 !important;
        border: none !important;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25) !important;
    }

    .qr-frame {
        background: #ffffff;
        padding: 12px;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
        display: inline-block;
        margin-bottom: 18px;
    }

    .qr-frame img {
        width: 170px;
        height: 170px;
        display: block;
        border-radius: 8px;
    }

    .details-box {
        background: rgba(0, 0, 0, 0.025);
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 14px;
        padding: 14px 16px;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin-bottom: 18px;
    }

    .details-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.84rem;
    }

    .details-row .label { color: var(--text-secondary); font-weight: 500; }
    .details-row .val { color: var(--text-heading); font-weight: 600; text-align: right; max-width: 60%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .details-row .val.mono { font-family: ui-monospace, monospace; background: rgba(0, 0, 0, 0.04); padding: 2px 6px; border-radius: 5px; font-size: 0.78rem; }

    @media (max-width: 960px) {
        .widgets-grid { grid-template-columns: 1fr; }
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
        border-color: var(--apple-blue);
        transform: translateY(-1px);
    }

    .upload-zone .upload-icon {
        font-size: 2.8rem;
        color: var(--apple-blue);
        margin-bottom: 12px;
        transition: transform 0.2s ease, color 0.2s ease;
    }

    .upload-zone:hover .upload-icon {
        transform: scale(1.08);
    }

    .upload-zone .upload-text-main {
        font-size: 0.90rem;
        font-weight: 600;
        color: var(--text-heading);
        margin-bottom: 4px;
    }

    .upload-zone .upload-text-hint {
        font-size: 0.78rem;
        color: var(--text-secondary);
    }

    #file-name {
        display: inline-block;
        margin-top: 10px;
        font-size: 0.84rem;
        font-weight: 600;
        color: var(--apple-blue);
        word-break: break-all;
    }

    /* Estado cuando se selecciona un archivo (Feedback de Éxito) */
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
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

<!-- ── Encabezado Neo-Corporate ── -->
<div class="dashboard-neo-header">
    <div class="dashboard-neo-titles">
        <h1 class="dashboard-main-title">Panel de Control & Certificación</h1>
        <p class="dashboard-header-desc">Emisión de credenciales con firma digital y verificación mediante códigos QR criptográficos.</p>
    </div>
    
    <!-- Botones superiores: "Carga Excel" (blanco/verde) y "Nuevo Registro" (índigo/blanco) -->
    <div class="dashboard-neo-actions">
        <button type="button" class="btn-action-excel" id="btnCargaExcel" title="Carga masiva desde archivo Excel">
            <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
            <span>Carga Excel</span>
        </button>

        <button type="button" class="btn-action-indigo" id="btnNuevoRegistro" title="Registrar un nuevo funcionario institucional">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            <span>Nuevo Registro</span>
        </button>
    </div>
</div>

<!-- ── 1. Tarjetas Bento de Métricas (3 Tarjetas con Marca de Agua) ── -->
<div class="bento-grid">
    
    <!-- Tarjeta 1: Total Funcionarios -->
    <div class="bento-card">
        <div class="bento-card-top">
            <div class="bento-card-icon indigo" aria-hidden="true">
                <i class="fa-solid fa-users"></i>
            </div>
            <span class="bento-pill-tag tag-indigo">TOTAL</span>
        </div>
        <div class="bento-card-values">
            <span class="bento-main-number"><?= number_format($totalFuncionarios) ?></span>
        </div>
        <div class="bento-card-label">Total Funcionarios</div>
        <!-- Marca de agua translúcida al fondo derecho: Índigo -->
        <i class="fa-solid fa-users bento-watermark-icon watermark-indigo" aria-hidden="true"></i>
    </div>

    <!-- Tarjeta 2: Registrados Hoy -->
    <div class="bento-card">
        <div class="bento-card-top">
            <div class="bento-card-icon emerald" aria-hidden="true">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <span class="bento-pill-tag tag-emerald">HOY</span>
        </div>
        <div class="bento-card-values">
            <span class="bento-main-number"><?= number_format($registradosHoy) ?></span>
        </div>
        <div class="bento-card-label">Registrados Hoy</div>
        <!-- Marca de agua translúcida al fondo derecho: Verde Esmeralda -->
        <i class="fa-solid fa-calendar-check bento-watermark-icon watermark-emerald" aria-hidden="true"></i>
    </div>

    <!-- Tarjeta 3: Certificados QR Emitidos -->
    <div class="bento-card">
        <div class="bento-card-top">
            <div class="bento-card-icon purple" aria-hidden="true">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <span class="bento-pill-tag tag-purple">EMITIDOS</span>
        </div>
        <div class="bento-card-values">
            <span class="bento-main-number"><?= number_format($totalFuncionarios) ?></span>
        </div>
        <div class="bento-card-label">Certificados QR Activos</div>
        <!-- Marca de agua translúcida al fondo derecho: Púrpura -->
        <i class="fa-solid fa-qrcode bento-watermark-icon watermark-purple" aria-hidden="true"></i>
    </div>

</div>

<!-- ── 2. Tarjeta Flotante Principal (.content-card) con la Tabla de Funcionarios ── -->
<div class="content-card">
    
    <!-- Cabecera de la Tabla: Punto azul, título, píldora indicadora, barra de búsqueda y refresco -->
    <div class="table-neo-header">
        <div class="table-neo-title-group">
            <span class="table-dot-blue" aria-hidden="true"></span>
            <h3 class="table-neo-title-text">Últimos Funcionarios Registrados</h3>
            <span class="table-indicator-pill" id="badgeTotalEntradas"><?= count($ultimosRegistros) ?> entradas</span>
        </div>

        <div class="table-neo-controls">
            <!-- Barra de Búsqueda -->
            <div class="table-search-bar">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="text" id="filtroTablaDashboard" class="table-search-input" placeholder="Buscar funcionario..." autocomplete="off">
            </div>

            <!-- Botón de Refresco -->
            <button type="button" class="table-btn-refresh" id="btnRefrescarTabla" onclick="refrescarDashboard()" title="Actualizar registros">
                <i class="fa-solid fa-rotate-right" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="clean-table-container table-responsive">
        <?php if (empty($ultimosRegistros)): ?>
            <div class="empty-message">
                <p>No hay funcionarios registrados todavía. Utiliza "Nuevo Registro" o "Carga Excel".</p>
            </div>
        <?php else: ?>
            <table class="clean-table" id="tablaFuncionariosDashboard">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Funcionario / Cargo</th>
                        <th>Departamento</th>
                        <th>Fecha Registro</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimosRegistros as $funcionario): 
                        $cedula = htmlspecialchars((string)($funcionario['cedula'] ?? ''));
                        $nombre = htmlspecialchars((string)($funcionario['nombre_completo'] ?? '—'));
                        $cargo = htmlspecialchars((string)($funcionario['cargo'] ?? '—'));
                        $departamento = htmlspecialchars((string)($funcionario['departamento'] ?? '—'));
                        $fechaFormato = !empty($funcionario['fecha_registro']) ? date('d/m/Y h:i A', strtotime((string)$funcionario['fecha_registro'])) : 'Reciente';
                    ?>
                        <tr class="table-row-item">
                            <td>
                                <!-- Cédula formateada como píldora redondeada con fondo pastel y texto fuerte -->
                                <span class="cell-pill-cedula"><?= $cedula ?: '—' ?></span>
                            </td>
                            <td>
                                <span class="cell-title"><?= $nombre ?></span>
                                <span class="cell-subtitle"><?= $cargo ?></span>
                            </td>
                            <td>
                                <?= $departamento ?>
                            </td>
                            <td>
                                <?= $fechaFormato ?>
                            </td>
                            <td>
                                <!-- Estado "QR Emitido" (verde) formateado como píldora redondeada con fondo pastel y texto fuerte -->
                                <span class="cell-pill-status">
                                    <span class="cell-status-dot-green" aria-hidden="true"></span>
                                    QR Emitido
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <!-- Botón "Ver QR" (azul claro) formateado como píldora redondeada con fondo pastel y texto fuerte -->
                                <button 
                                    type="button" 
                                    class="btn-pill-view-qr" 
                                    onclick="verFuncionario('<?= $cedula ?>')"
                                    title="Ver código QR y credencial institucional"
                                >
                                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                                    <span>Ver QR</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div> <!-- /.content-card -->
</div> <!-- /.content-wrapper -->

<!-- ── Modales de Apoyo (Nuevo Registro y Carga Excel - Fuera de .content-wrapper) ── -->
<div class="modal-overlay" id="modalRegistro" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;align-items:center;justify-content:center;padding:20px;">
    <div class="modal-content" style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 28px 60px rgba(0,0,0,0.16);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:700;">Nuevo Registro de Funcionario</h3>
            <button type="button" id="btnCerrarRegistro" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:#86868b;">✕</button>
        </div>
        <form id="formRegistroManual" autocomplete="off" style="padding:22px 24px;">
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.77rem;font-weight:600;color:#86868b;margin-bottom:5px;">Cédula de Identidad</label>
                <input type="text" id="reg_cedula" name="cedula" style="width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;outline:none;" placeholder="Ej: V-12345678" required>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.77rem;font-weight:600;color:#86868b;margin-bottom:5px;">Nombre Completo</label>
                <input type="text" id="reg_nombre" name="nombre" style="width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;outline:none;" placeholder="Ej: Carlos Silva" required>
            </div>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:0.77rem;font-weight:600;color:#86868b;margin-bottom:5px;">Cargo Institucional</label>
                <input type="text" id="reg_cargo" name="cargo" style="width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;outline:none;" placeholder="Ej: Oficial Docente" required>
            </div>
            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:0.77rem;font-weight:600;color:#86868b;margin-bottom:5px;">Departamento</label>
                <input type="text" id="reg_departamento" name="departamento" style="width:100%;height:40px;padding:0 12px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;outline:none;" placeholder="Ej: Dirección Académica" required>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="btnCancelarRegistro" style="height:38px;padding:0 16px;border-radius:10px;background:none;border:1px solid rgba(0,0,0,0.1);cursor:pointer;color:#86868b;">Cancelar</button>
                <button type="submit" id="btnGuardarRegistro" style="height:38px;padding:0 18px;border-radius:10px;background:var(--apple-blue);border:none;color:#fff;font-weight:600;cursor:pointer;">Guardar y Generar QR</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalExcel" role="dialog" aria-modal="true" aria-hidden="true" style="display:none;align-items:center;justify-content:center;padding:20px;">
    <div class="modal-content" style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 28px 60px rgba(0,0,0,0.16);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid rgba(0,0,0,0.06);display:flex;align-items:center;justify-content:space-between;">
            <h3 style="margin:0;font-size:1.05rem;font-weight:700;">Carga Masiva desde Excel</h3>
            <button type="button" id="btnCerrarExcel" style="background:none;border:none;font-size:1.1rem;cursor:pointer;color:#86868b;">✕</button>
        </div>
        <form id="formCargaExcel" enctype="multipart/form-data" autocomplete="off" style="padding:22px 24px;">
            <p style="font-size:0.82rem;color:#86868b;margin:0 0 16px 0;line-height:1.4;">
                El archivo <strong>.xlsx</strong> debe tener en la primera fila los encabezados: <em>Cédula, Nombre, Cargo, Departamento</em>.
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

            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="btnCancelarExcel" style="height:38px;padding:0 16px;border-radius:10px;background:none;border:1px solid rgba(0,0,0,0.1);cursor:pointer;color:#86868b;">Cancelar</button>
                <button type="submit" id="btnProcesarExcel" style="height:38px;padding:0 18px;border-radius:10px;background:var(--apple-blue);border:none;color:#fff;font-weight:600;cursor:pointer;">Importar y Procesar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ── JavaScript Específico del Dashboard ── -->
<script>
/**
 * Consulta la API y despliega el modal SweetAlert2 con el QR en Base64 y datos del funcionario.
 * @param {string} cedula
 */
async function verFuncionario(cedula) {
    if (!cedula) return;

    // 1. Alerta de "Cargando..."
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
        // 2. Fetch a BASE_URL + '/api/funcionario?cedula=' + cedula
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

        // 3. Modal SweetAlert2 personalizado (borde 24px, QR centrado y detalles)
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

// Controladores de Modales de Registro y Excel
(function () {
    'use strict';

    const modalReg = document.getElementById('modalRegistro');
    const modalExc = document.getElementById('modalExcel');

    document.getElementById('btnNuevoRegistro')?.addEventListener('click', () => {
        document.getElementById('formRegistroManual')?.reset();
        modalReg.style.display = 'flex';
        document.getElementById('reg_cedula')?.focus();
    });

    document.getElementById('btnCargaExcel')?.addEventListener('click', () => {
        document.getElementById('formCargaExcel')?.reset();
        modalExc.style.display = 'flex';
    });

    document.getElementById('btnCerrarRegistro')?.addEventListener('click', () => modalReg.style.display = 'none');
    document.getElementById('btnCancelarRegistro')?.addEventListener('click', () => modalReg.style.display = 'none');
    document.getElementById('btnCerrarExcel')?.addEventListener('click', () => modalExc.style.display = 'none');
    document.getElementById('btnCancelarExcel')?.addEventListener('click', () => modalExc.style.display = 'none');

    [modalReg, modalExc].forEach(m => {
        m?.addEventListener('click', (e) => {
            if (e.target === m) m.style.display = 'none';
        });
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (modalReg) modalReg.style.display = 'none';
            if (modalExc) modalExc.style.display = 'none';
        }
    });

    // Envío Formulario Registro Manual
    document.getElementById('formRegistroManual')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const cedula = document.getElementById('reg_cedula').value.trim();
        const nombre = document.getElementById('reg_nombre').value.trim();
        const cargo = document.getElementById('reg_cargo').value.trim();
        const depto = document.getElementById('reg_departamento').value.trim();

        if (!cedula || !nombre || !cargo || !depto) return;

        try {
            const formData = new FormData();
            formData.append('cedula', cedula);
            formData.append('nombre', nombre);
            formData.append('cargo', cargo);
            formData.append('departamento', depto);

            const res = await fetch(BASE_URL + '/registro-manual', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.exito) {
                modalReg.style.display = 'none';
                await Swal.fire({
                    title: '¡Funcionario Registrado!',
                    text: data.mensaje,
                    icon: 'success',
                    confirmButtonText: 'Actualizar',
                    customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                    buttonsStyling: false
                });
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.mensaje || 'Error al registrar',
                    icon: 'error',
                    confirmButtonText: 'Revisar',
                    customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                    buttonsStyling: false
                });
            }
        } catch (err) {
            console.error(err);
        }
    });

    // Tarea 3: Dinámica de la Zona de Subida Excel
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

    document.getElementById('btnCargaExcel')?.addEventListener('click', () => {
        if (fileNameSpan) fileNameSpan.textContent = '';
        uploadZone?.classList.remove('file-selected');
    });

    // Envío Carga Excel
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
                modalExc.style.display = 'none';
                await Swal.fire({
                    title: '¡Carga Completada!',
                    text: data.mensaje || 'Funcionarios importados exitosamente.',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                    buttonsStyling: false
                });
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.mensaje || 'Error al procesar el archivo Excel',
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
                text: 'No fue posible comunicar con el servidor.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
                customClass: { popup: 'apple-swal-modal', confirmButton: 'apple-swal-btn' },
                buttonsStyling: false
            });
        }
    });

    // ── Búsqueda Dinámica en Tiempo Real de la Tabla ──
    const searchInput = document.getElementById('filtroTablaDashboard');
    const tableRows   = document.querySelectorAll('#tablaFuncionariosDashboard tbody tr.table-row-item');
    const badgeCount  = document.getElementById('badgeTotalEntradas');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (badgeCount) {
                badgeCount.textContent = visibleCount + (visibleCount === 1 ? ' entrada' : ' entradas');
            }
        });
    }

    // ── Refresco con Efecto de Giro ──
    window.refrescarDashboard = function () {
        const btn = document.getElementById('btnRefrescarTabla');
        if (btn) {
            btn.querySelector('i')?.classList.add('fa-spin');
        }
        setTimeout(() => {
            window.location.reload();
        }, 350);
    };
})();
</script>
