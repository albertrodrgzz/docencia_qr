<?php
$page_title = 'Reportes';

/**
 * Vista de Reportes y Exportación Reactiva en Tiempo Real — ISPEB Sistema Institucional
 * Ecosistema macOS / Apple HIG con Live Search, Debounce y Vista Previa sin recargas.
 *
 * @var string|null $tituloVista       Título para topbar y <title>
 * @var bool|null   $mostrarBotonAtras Controla el botón circular de regresar
 */

$tituloVista       = 'Reportes y Exportaciones';
$mostrarBotonAtras = true;

// Cargar listas dinámicas de Departamentos y Cargos para poblar los filtros
$departamentos = [];
$cargos = [];

try {
    $pdo = \Configuracion\ConexionBD::getInstancia();
    $departamentos = $pdo->query("SELECT DISTINCT departamento FROM funcionarios_registrados WHERE departamento IS NOT NULL AND TRIM(departamento) != '' ORDER BY departamento ASC")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    $cargos = $pdo->query("SELECT DISTINCT cargo FROM funcionarios_registrados WHERE cargo IS NOT NULL AND TRIM(cargo) != '' ORDER BY cargo ASC")->fetchAll(\PDO::FETCH_COLUMN) ?: [];
} catch (\Throwable $e) {
    $departamentos = ['Dirección de Docencia', 'Coordinación Académica', 'Seguridad Ciudadana', 'Recursos Humanos', 'Tecnología'];
    $cargos = ['Director General', 'Coordinador', 'Docente Instructor', 'Analista', 'Oficial'];
}

require_once __DIR__ . '/layout/header.php';
?>

<style>
    /* ── Estilos Específicos: Reportes y Emisión (Neo-Corporate) ── */

    /* ── Cabecera Principal Hero ── */
    .reportes-header-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .reportes-header-titles {
        max-width: 650px;
    }

    .reportes-main-title {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #1e1b4b;
        margin: 0 0 6px 0;
    }

    .reportes-subtitle {
        font-size: 0.90rem;
        color: #64748b;
        margin: 0;
        line-height: 1.45;
    }

    .reportes-header-pills {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .trust-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 0.80rem;
        font-weight: 600;
        background: #ffffff;
        color: #334155;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .live-dot-emerald {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
        display: inline-block;
    }

    .text-shield-blue {
        color: #2563eb;
    }

    /* ── Tarea 1: Cuadrícula CSS Grid (Dos Columnas) ── */
    .reportes-layout {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 24px;
        align-items: start;
        width: 100%;
        max-width: 100%;
    }

    /* Cambiamos el breakpoint de 1024px a 1200px para pantallas medianas con Sidebar */
    @media (max-width: 1200px) {
        .reportes-layout {
            grid-template-columns: 1fr !important;
            gap: 20px;
        }
        /* El panel de filtros dejará de ser una columna lateral y se apilará arriba */
        .filter-card {
            margin-bottom: 20px;
        }
    }

    /* ── Columna Izquierda: Panel de Filtros (.filter-card) ── */
    .filter-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }

    .filter-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .filter-card-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(55, 48, 163, 0.08);
        color: #3730a3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .filter-title-wrap h3 {
        font-size: 1.02rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1e1b4b;
        margin: 0;
    }

    .filter-title-wrap p {
        font-size: 0.76rem;
        color: #64748b;
        margin: 2px 0 0 0;
    }

    .pill-auto-blue {
        font-size: 0.72rem;
        font-weight: 700;
        color: #2563eb;
        background: #eff6ff;
        padding: 3px 9px;
        border-radius: 12px;
        border: 1px solid rgba(37, 99, 235, 0.15);
    }

    /* Inputs y Selects sin bordes duros con fondo gris ultra claro */
    .form-group-filter {
        margin-bottom: 18px;
    }

    .filter-label {
        display: block;
        font-size: 0.70rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filter-input-wrap {
        position: relative;
    }

    .filter-input-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.88rem;
        pointer-events: none;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        background: #f3f4f6;
        border: 1px solid transparent;
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.88rem;
        color: #1e293b;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .filter-input-wrap .filter-input {
        padding-left: 38px;
    }

    .filter-input:focus,
    .filter-select:focus {
        background: #ffffff;
        border-color: rgba(55, 48, 163, 0.3);
        box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.10);
    }

    .filter-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
        cursor: pointer;
    }

    /* Caja de Rango de Fechas Desplegable */
    .fechas-custom-box {
        margin-top: 10px;
        padding: 12px;
        background: #eef2f6;
        border-radius: 12px;
        display: none;
        animation: fadeUp 0.25s ease-out forwards;
    }

    .fechas-custom-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .fechas-custom-row label {
        font-size: 0.70rem;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .fechas-custom-row input[type="date"] {
        height: 38px;
        font-size: 0.82rem;
        padding: 0 8px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 8px;
    }

    /* Botón Limpiar Filtros */
    .btn-reset-filters {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        height: 40px;
        margin-top: 8px;
        background: #f1f5f9;
        color: #475569;
        font-family: inherit;
        font-size: 0.84rem;
        font-weight: 600;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .btn-reset-filters:hover {
        background: #e2e8f0;
        color: #1e1b4b;
    }

    /* Caja Informativa Footer del Panel */
    .filter-info-box {
        margin-top: 20px;
        padding: 14px 16px;
        background: #f3f4f6;
        border-radius: 14px;
        border: none;
    }

    .filter-info-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }

    .info-label-caps {
        font-size: 0.70rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .info-badge-blue {
        font-size: 0.72rem;
        font-weight: 700;
        color: #2563eb;
        background: #ffffff;
        padding: 2px 8px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .info-desc-text {
        font-size: 0.76rem;
        color: #475569;
        font-weight: 500;
    }

    /* ── Columna Derecha: Resultados y Acción (Flotante) ── */
    .reportes-results-column {
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-width: 0;
        max-width: 100%;
        width: 100%;
    }

    .results-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        width: 100%;
    }

    .results-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .results-count-title {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        color: #1e1b4b;
        margin: 0;
    }

    .preview-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 600;
        background: #ecfdf5;
        color: #065f46;
    }

    .preview-badge-status .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 6px rgba(16, 185, 129, 0.6);
    }

    /* Botón Gigante "Exportar QRs a PDF" */
    .btn-giant-export,
    .btn-export-pdf {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        height: 48px;
        padding: 0 28px;
        white-space: nowrap;
        background: #3730a3;
        color: #ffffff;
        font-family: inherit;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 10px 25px rgba(55, 48, 163, 0.32);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        flex-shrink: 0;
    }

    .btn-giant-export:hover,
    .btn-export-pdf:hover {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(55, 48, 163, 0.40);
        color: #ffffff;
    }

    .btn-giant-export:active,
    .btn-export-pdf:active {
        transform: scale(0.98);
    }

    .btn-giant-export:disabled,
    .btn-export-pdf:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* ── Tabla Edge-to-Edge Minimalista & Responsive ── */
    .table-container-edge,
    .table-container-responsive,
    .table-responsive {
        width: 100%;
        max-width: 100%; /* Evita que crezca más que su padre */
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.03);
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }

    .table-responsive table,
    .table-container-edge table,
    .table-minimal-edge,
    .table-minimal-ios {
        min-width: 900px; /* Fuerza a la tabla a mantener su diseño original */
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table-minimal-edge thead th,
    .table-minimal-ios thead th {
        padding: 16px 22px;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        background-color: #f8fafc;
        white-space: nowrap;
    }

    .table-minimal-edge tbody tr,
    .table-minimal-ios tbody tr {
        transition: background 0.18s ease;
    }

    .table-minimal-edge tbody tr:hover,
    .table-minimal-ios tbody tr:hover {
        background-color: #f8fafc;
    }

    .table-minimal-edge tbody td,
    .table-minimal-ios tbody td {
        padding: 16px 22px;
        font-size: 0.88rem;
        color: #1e293b;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }

    .table-minimal-edge tbody tr:last-child td,
    .table-minimal-ios tbody tr:last-child td {
        border-bottom: none;
    }

    /* Píldora de Cédula en gris claro */
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

    .cell-nombre {
        font-weight: 600;
        color: #1e1b4b;
        letter-spacing: -0.01em;
    }

    .cell-secondary {
        color: #475569;
        font-size: 0.85rem;
    }

    .empty-state-cell {
        text-align: center;
        padding: 48px 20px !important;
        color: #86868b;
    }

    .empty-state-cell i {
        font-size: 2rem;
        color: #cbd5e1;
        margin-bottom: 10px;
        display: block;
    }

    .loading-pulse {
        opacity: 0.5;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <!-- ── Tarea 2: Cabecera Principal (Fuera del Grid) ── -->
    <div class="reportes-header-hero">
        <div class="reportes-header-titles">
            <h1 class="reportes-main-title">Reportes y Emisión de QRs</h1>
            <p class="reportes-subtitle">Filtra funcionarios en tiempo real y exporta hojas de impresión con códigos QR institucionales.</p>
        </div>
        <div class="reportes-header-pills">
            <div class="trust-pill" title="Mecanismo de generación de QR activo en tiempo real">
                <span class="live-dot-emerald"></span>
                <span>Motor QR Activo</span>
            </div>
            <div class="trust-pill" title="Certificación de seguridad criptográfica">
                <i class="fa-solid fa-shield-halved text-shield-blue"></i>
                <span>Cert. SHA-256</span>
            </div>
        </div>
    </div>

    <!-- ── Tarea 1: Cuadrícula CSS Grid de Dos Columnas (.reportes-layout) ── -->
    <div class="reportes-layout">

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- Tarea 3: COLUMNA IZQUIERDA: Panel de Filtros (.filter-card)   -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <aside class="filter-card">
            <div class="filter-card-header">
                <div class="filter-card-header-left">
                    <div class="filter-icon-circle" aria-hidden="true">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div class="filter-title-wrap">
                        <h3>Filtros de Búsqueda</h3>
                        <p>Búsqueda reactiva en vivo</p>
                    </div>
                </div>
                <span class="pill-auto-blue">Auto</span>
            </div>

            <form id="form-filtros-live" onsubmit="event.preventDefault();" autocomplete="off">

                <!-- 1. Búsqueda por texto (Cédula/Nombre) -->
                <div class="form-group-filter">
                    <label for="filtro_busqueda" class="filter-label">Buscar por Cédula o Nombre</label>
                    <div class="filter-input-wrap">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input 
                            type="text" 
                            id="filtro_busqueda" 
                            name="busqueda" 
                            class="filter-input" 
                            placeholder="Ej: V-12345678 o Andrés..."
                            aria-label="Cédula o Nombre"
                        >
                    </div>
                </div>

                <!-- 2. Select de Departamento -->
                <div class="form-group-filter">
                    <label for="filtro_dept" class="filter-label">Departamento</label>
                    <select id="filtro_dept" name="dept" class="filter-select" aria-label="Seleccionar departamento">
                        <option value="">Todos los departamentos</option>
                        <?php foreach ($departamentos as $depto): ?>
                            <option value="<?= htmlspecialchars((string)$depto, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string)$depto, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select de Cargo Institucional -->
                <div class="form-group-filter">
                    <label for="filtro_cargo" class="filter-label">Cargo Institucional</label>
                    <select id="filtro_cargo" name="cargo" class="filter-select" aria-label="Seleccionar cargo">
                        <option value="">Todos los cargos</option>
                        <?php foreach ($cargos as $cargo): ?>
                            <option value="<?= htmlspecialchars((string)$cargo, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string)$cargo, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 3. Select de Rango de Fechas / Lote -->
                <div class="form-group-filter">
                    <label for="filtro_lote" class="filter-label">Rango de Fechas / Lote</label>
                    <select id="filtro_lote" name="lote" class="filter-select" aria-label="Rango de Fechas">
                        <option value="todos">Todos los registros</option>
                        <option value="24h">Últimas 24 horas</option>
                        <option value="semana">Última semana</option>
                        <option value="mes">Últimos 30 días</option>
                        <option value="personalizado">Personalizado (Desde / Hasta)</option>
                    </select>

                    <!-- Fechas Desde / Hasta (Desplegable si es personalizado) -->
                    <div id="cajaFechasCustom" class="fechas-custom-box">
                        <div class="fechas-custom-row">
                            <div>
                                <label for="fecha_desde" class="filter-label">Desde</label>
                                <input type="date" id="fecha_desde" name="fecha_desde" class="filter-input">
                            </div>
                            <div>
                                <label for="fecha_hasta" class="filter-label">Hasta</label>
                                <input type="date" id="fecha_hasta" name="fecha_hasta" class="filter-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Restablecer Filtros -->
                <button type="button" id="btnLimpiarFiltros" class="btn-reset-filters">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Restablecer Filtros</span>
                </button>

                <!-- Caja Informativa (Footer del panel de filtros) -->
                <div class="filter-info-box">
                    <div class="filter-info-top">
                        <span class="info-label-caps">Formato de Salida</span>
                        <span class="info-badge-blue">PDF/X-4 CMYK</span>
                    </div>
                    <div class="info-desc-text">Grilla de 8 tarjetas por pliego A4</div>
                </div>

            </form>
        </aside>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- Tarea 4: COLUMNA DERECHA: Resultados y Acción (Flotante)      -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <main class="reportes-results-column">

            <!-- Header de Resultados: Conteo y Botón Gigante de Exportación -->
            <div class="results-action-bar">
                <div class="results-header-info">
                    <h3 class="results-count-title" id="texto-resultados">
                        Resultados: <span id="contador-funcionarios">0</span> funcionarios
                    </h3>
                    <span class="preview-badge-status" id="badge-estado-live">
                        <span class="status-dot"></span>
                        <span>En vivo</span>
                    </span>
                </div>

                <button type="button" class="btn-giant-export" id="btnExportarPdf" title="Generar hoja de impresión PDF con los registros actuales">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Exportar QRs a PDF</span>
                </button>
            </div>

            <!-- Tabla Edge-to-Edge Minimalista -->
            <div class="table-container-edge table-responsive" id="contenedorTablaReportes">
                <table class="table-minimal-edge" id="tablaPreviewReportes">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre Completo</th>
                            <th>Departamento</th>
                            <th>Cargo</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody id="tablaReportesBody">
                        <tr>
                            <td colspan="5" class="empty-state-cell">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                Criterios cargando...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </main>

    </div> <!-- /.reportes-layout -->

</div> <!-- /.content-wrapper -->

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- Tarea 2: La Magia JavaScript - Live Search con Debounce (Vanilla JS)     -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBusqueda     = document.getElementById('filtro_busqueda');
    const selectDept        = document.getElementById('filtro_dept');
    const selectCargo       = document.getElementById('filtro_cargo');
    const selectLote        = document.getElementById('filtro_lote');
    const inputFechaDesde   = document.getElementById('fecha_desde');
    const inputFechaHasta   = document.getElementById('fecha_hasta');
    const cajaFechasCustom  = document.getElementById('cajaFechasCustom');
    const btnLimpiar        = document.getElementById('btnLimpiarFiltros');
    const btnExportarPdf    = document.getElementById('btnExportarPdf');

    const tablaBody         = document.getElementById('tablaReportesBody');
    const contenedorTabla   = document.getElementById('contenedorTablaReportes');
    const contadorSpan      = document.getElementById('contador-funcionarios');

    // Despliegue de fechas personalizadas
    selectLote?.addEventListener('change', function () {
        if (this.value === 'personalizado') {
            cajaFechasCustom.style.display = 'block';
            inputFechaDesde?.focus();
        } else {
            cajaFechasCustom.style.display = 'none';
        }
    });

    /**
     * CRÍTICO: Función Debounce estándar de 300ms
     * Retrasa la ejecución de la función hasta que el usuario pause la escritura.
     */
    function debounce(func, delay = 300) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    }

    /**
     * Construye los Query Params a partir de los controles activos
     */
    function construirParams() {
        const params = new URLSearchParams();

        const valBusqueda = (document.getElementById('filtro_busqueda')?.value || '').trim();
        const valDept     = (document.getElementById('filtro_dept')?.value || '').trim();
        const valCargo    = (document.getElementById('filtro_cargo')?.value || '').trim();
        const valLote     = (document.getElementById('filtro_lote')?.value || 'todos').trim();
        const valDesde    = (document.getElementById('fecha_desde')?.value || '').trim();
        const valHasta    = (document.getElementById('fecha_hasta')?.value || '').trim();

        if (valBusqueda !== '') params.append('busqueda', valBusqueda);
        if (valDept !== '')     params.append('departamento', valDept);
        if (valCargo !== '')    params.append('cargo', valCargo);
        if (valLote !== '' && valLote !== 'todos') params.append('lote', valLote);
        if (valLote === 'personalizado') {
            if (valDesde !== '') params.append('fecha_desde', valDesde);
            if (valHasta !== '') params.append('fecha_hasta', valHasta);
        }

        return params;
    }

    // Almacena los últimos registros cargados para control de exportación
    let registrosActuales = [];

    /**
     * Función obtenerReporte()
     * Recolecta filtros, captura 'cargo', hace fetch a la API y reconstruye dinámicamente el tbody.
     */
    function obtenerReporte() {
        contenedorTabla.classList.add('loading-pulse');

        // Tarea 1: Captura exacta del valor del select de cargo y demás filtros
        const busqueda = (document.getElementById('filtro_busqueda')?.value || '').trim();
        const dept     = (document.getElementById('filtro_dept')?.value || '').trim();
        const cargo    = (document.getElementById('filtro_cargo')?.value || '').trim();
        const lote     = (document.getElementById('filtro_lote')?.value || 'todos').trim();
        const desde    = (document.getElementById('fecha_desde')?.value || '').trim();
        const hasta    = (document.getElementById('fecha_hasta')?.value || '').trim();

        const params = new URLSearchParams();
        if (busqueda !== '') params.append('busqueda', busqueda);
        if (dept !== '')     params.append('departamento', dept);
        if (cargo !== '')    params.append('cargo', cargo);
        if (lote !== '' && lote !== 'todos') params.append('lote', lote);
        if (lote === 'personalizado') {
            if (desde !== '') params.append('fecha_desde', desde);
            if (hasta !== '') params.append('fecha_hasta', hasta);
        }

        const endpoint = `${BASE_URL}/api/reportes/filtrar?${params.toString()}`;

        fetch(endpoint, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error HTTP ' + response.status);
            }
            return response.json();
        })
        .then(res => {
            contenedorTabla.classList.remove('loading-pulse');

            if (!res || (res.estado !== 'ok' && !res.exito)) {
                renderizarVacio('No se pudo procesar la solicitud.');
                return;
            }

            const funcionarios = Array.isArray(res.datos) ? res.datos : [];
            registrosActuales = funcionarios;

            // Actualizar encabezado: "Resultados: X funcionarios"
            contadorSpan.textContent = funcionarios.length;

            if (funcionarios.length === 0) {
                renderizarVacio('No se encontraron funcionarios que coincidan con los filtros aplicados.');
                btnExportarPdf.disabled = true;
                return;
            }

            btnExportarPdf.disabled = false;
            renderizarFilas(funcionarios);
        })
        .catch(err => {
            contenedorTabla.classList.remove('loading-pulse');
            console.error('[obtenerReporte Error]', err);
            renderizarVacio('Error al consultar los registros del servidor.');
        });
    }

    /**
     * Inyecta dinámicamente las filas <tr> en el <tbody>
     */
    function renderizarFilas(lista) {
        tablaBody.innerHTML = '';

        const fragment = document.createDocumentFragment();

        lista.forEach(func => {
            const tr = document.createElement('tr');

            // Sanear textos contra XSS
            const cedula = escapeHTML(func.cedula || 'S/C');
            const nombre = escapeHTML(func.nombre_completo || 'Sin nombre');
            const dept   = escapeHTML(func.departamento || 'No asignado');
            const cargo  = escapeHTML(func.cargo || 'Funcionario');
            const fecha  = func.fecha_registro ? escapeHTML(String(func.fecha_registro).substring(0, 10)) : 'N/A';

            tr.innerHTML = `
                <td><span class="badge-cedula-cell">${cedula}</span></td>
                <td><strong class="cell-nombre">${nombre}</strong></td>
                <td class="cell-secondary">${dept}</td>
                <td class="cell-secondary">${cargo}</td>
                <td class="cell-secondary">${fecha}</td>
            `;

            fragment.appendChild(tr);
        });

        tablaBody.appendChild(fragment);
    }

    /**
     * Renderiza estado vacío cuando no hay registros
     */
    function renderizarVacio(mensaje) {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="5" class="empty-state-cell">
                    <i class="fa-solid fa-users-slash" aria-hidden="true"></i>
                    <p style="margin: 0; font-size: 0.90rem;">${escapeHTML(mensaje)}</p>
                </td>
            </tr>
        `;
        contadorSpan.textContent = '0';
    }

    /**
     * Utilidad para evitar inyección XSS
     */
    function escapeHTML(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Asignación de Event Listeners ──

    // 1. Debounce de 300ms en el input de texto (Cédula o Nombre)
    const busquedaDebounced = debounce(function () {
        obtenerReporte();
    }, 300);

    inputBusqueda?.addEventListener('input', busquedaDebounced);

    // 2. Selects con evento change inmediato
    selectDept?.addEventListener('change', obtenerReporte);
    selectCargo?.addEventListener('change', obtenerReporte);
    selectLote?.addEventListener('change', obtenerReporte);
    inputFechaDesde?.addEventListener('change', obtenerReporte);
    inputFechaHasta?.addEventListener('change', obtenerReporte);

    // 3. Botón Restablecer
    btnLimpiar?.addEventListener('click', function () {
        inputBusqueda.value = '';
        selectDept.value = '';
        selectCargo.value = '';
        selectLote.value = 'todos';
        inputFechaDesde.value = '';
        inputFechaHasta.value = '';
        cajaFechasCustom.style.display = 'none';

        obtenerReporte();

        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'info',
                title: 'Filtros restablecidos'
            });
        }
    });

    // 4. Botón "Exportar QRs a PDF"
    btnExportarPdf?.addEventListener('click', function () {
        if (registrosActuales.length === 0) {
            if (typeof AppleAlert !== 'undefined') {
                AppleAlert.fire({
                    icon: 'warning',
                    title: 'Sin Registros',
                    text: 'No hay funcionarios en la lista actual para exportar a PDF.'
                });
            }
            return;
        }

        const params = construirParams();
        const urlPdf = BASE_URL + '/reportes/pdf' + (params.toString() ? '?' + params.toString() : '');

        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'info',
                title: 'Generando PDF con ' + registrosActuales.length + ' funcionario(s)...'
            });
        }

        // Abrir PDF en pestaña nueva para previsualización e impresión
        window.open(urlPdf, '_blank');
    });

    // ── Carga Inicial Inmediata ──
    obtenerReporte();
});
</script>
