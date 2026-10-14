<?php
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
    /* ── Contenedor General y Encabezado ── */
    .reportes-view-header {
        margin-bottom: 24px;
    }

    .reportes-view-header h2 {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        color: #1d1d1f;
        margin: 0 0 6px 0;
    }

    .reportes-view-header p {
        font-size: 0.90rem;
        color: #86868b;
        margin: 0;
        line-height: 1.45;
    }

    /* ── Tarea 1: Cuadrícula CSS Grid (Dos Columnas) ── */
    .reportes-reactive-grid {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 28px;
        align-items: start;
    }

    @media (max-width: 1080px) {
        .reportes-reactive-grid {
            grid-template-columns: 1fr;
            gap: 24px;
        }
    }

    /* ── Panel Lateral Izquierdo (Filtros) ── */
    .panel-filtros-side {
        background: #fbfbfd;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 18px;
        padding: 24px 22px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .panel-filtros-side .panel-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .panel-filtros-side .panel-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(0, 113, 227, 0.09);
        color: var(--apple-blue, #0071e3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .panel-filtros-side .panel-title-text h3 {
        font-size: 1.02rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1d1d1f;
        margin: 0;
    }

    .panel-filtros-side .panel-title-text p {
        font-size: 0.78rem;
        color: #86868b;
        margin: 2px 0 0 0;
    }

    /* Inputs y Selects Redondeados Estilo Apple */
    .form-group-filter {
        margin-bottom: 18px;
    }

    .form-group-filter label {
        display: block;
        font-size: 0.80rem;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 7px;
        letter-spacing: -0.01em;
    }

    .filter-input-wrap {
        position: relative;
    }

    .filter-input-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #86868b;
        font-size: 0.88rem;
        pointer-events: none;
    }

    .filter-input,
    .filter-select {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.88rem;
        color: #1d1d1f;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .filter-input-wrap .filter-input {
        padding-left: 38px;
    }

    .filter-input:focus,
    .filter-select:focus {
        border-color: #0071e3;
        box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
    }

    .filter-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2386868b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
        cursor: pointer;
    }

    /* Caja de Rango de Fechas Desplegable */
    .fechas-custom-box {
        margin-top: 10px;
        padding: 12px;
        background: #f5f5f7;
        border: 1px solid rgba(0, 0, 0, 0.04);
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
        font-size: 0.74rem;
        color: #86868b;
        margin-bottom: 4px;
    }

    .fechas-custom-row input[type="date"] {
        height: 36px;
        font-size: 0.82rem;
        padding: 0 8px;
    }

    /* Botón Limpiar Filtros */
    .btn-reset-filters {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        height: 38px;
        margin-top: 6px;
        background: rgba(0, 0, 0, 0.04);
        color: #515154;
        font-family: inherit;
        font-size: 0.84rem;
        font-weight: 600;
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .btn-reset-filters:hover {
        background: rgba(0, 0, 0, 0.08);
        color: #1d1d1f;
    }

    /* ── Panel Derecho: Vista Previa y Exportación ── */
    .panel-preview-main {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    .preview-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .preview-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .preview-result-count {
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1d1d1f;
        margin: 0;
    }

    .preview-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.74rem;
        font-weight: 600;
        background: rgba(0, 113, 227, 0.08);
        color: #0071e3;
    }

    .preview-badge-status .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #34c759;
        box-shadow: 0 0 6px rgba(52, 199, 89, 0.6);
    }

    /* Botón "Exportar QRs a PDF" */
    .btn-export-pdf {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        height: 42px;
        padding: 0 22px;
        background: linear-gradient(135deg, #0071e3 0%, #0051c6 100%);
        color: #ffffff;
        font-family: inherit;
        font-size: 0.90rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(0, 113, 227, 0.28);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-export-pdf:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 113, 227, 0.36);
        color: #ffffff;
    }

    .btn-export-pdf:active {
        transform: scale(0.98);
    }

    .btn-export-pdf:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    /* ── Tabla Minimalista Estilo iOS ── */
    .table-container-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
    }

    .table-minimal-ios {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table-minimal-ios thead th {
        padding: 13px 18px;
        font-size: 0.74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #86868b;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        background-color: #fafafa;
        white-space: nowrap;
    }

    .table-minimal-ios tbody tr {
        transition: background 0.18s ease;
    }

    .table-minimal-ios tbody tr:hover {
        background-color: #f7f7f9;
    }

    .table-minimal-ios tbody td {
        padding: 14px 18px;
        font-size: 0.88rem;
        color: #1d1d1f;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }

    .table-minimal-ios tbody tr:last-child td {
        border-bottom: none;
    }

    .badge-cedula-cell {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.80rem;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.04);
        color: #1d1d1f;
    }

    .cell-nombre {
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.01em;
    }

    .cell-secondary {
        color: #6e6e73;
        font-size: 0.84rem;
    }

    .empty-state-cell {
        text-align: center;
        padding: 48px 20px !important;
        color: #86868b;
    }

    .empty-state-cell i {
        font-size: 2rem;
        color: #c7c7cc;
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

<!-- ── Contenedor Principal (.content-card) ── -->
<div class="content-card">

    <div class="reportes-view-header">
        <h2>Reportes y Emisión de QRs</h2>
        <p>Filtra funcionarios en tiempo real y exporta hojas de impresión masiva con códigos QR listos para producción.</p>
    </div>

    <!-- ── Tarea 1: Cuadrícula CSS Grid de Dos Columnas ── -->
    <div class="reportes-reactive-grid">

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- COLUMNA IZQUIERDA: Panel Lateral Gris Claro (Filtros)         -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <aside class="panel-filtros-side">
            <div class="panel-header">
                <div class="panel-icon" aria-hidden="true">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="panel-title-text">
                    <h3>Filtros de Búsqueda</h3>
                    <p>Búsqueda reactiva en vivo</p>
                </div>
            </div>

            <form id="form-filtros-live" onsubmit="event.preventDefault();" autocomplete="off">

                <!-- 1. Búsqueda por texto (Cédula/Nombre) -->
                <div class="form-group-filter">
                    <label for="filtro_busqueda">Buscar por Cédula o Nombre</label>
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
                    <label for="filtro_dept">Departamento</label>
                    <select id="filtro_dept" name="dept" class="filter-select" aria-label="Seleccionar departamento">
                        <option value="">Todos los departamentos</option>
                        <?php foreach ($departamentos as $depto): ?>
                            <option value="<?= htmlspecialchars((string)$depto, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars((string)$depto, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Select Opcional de Cargo para afinamiento -->
                <div class="form-group-filter">
                    <label for="filtro_cargo">Cargo Institucional</label>
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
                    <label for="filtro_lote">Rango de Fechas / Lote</label>
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
                                <label for="fecha_desde">Desde</label>
                                <input type="date" id="fecha_desde" name="fecha_desde" class="filter-input">
                            </div>
                            <div>
                                <label for="fecha_hasta">Hasta</label>
                                <input type="date" id="fecha_hasta" name="fecha_hasta" class="filter-input">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón de Limpiar / Restablecer -->
                <button type="button" id="btnLimpiarFiltros" class="btn-reset-filters">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Restablecer Filtros</span>
                </button>

            </form>
        </aside>

        <!-- ══════════════════════════════════════════════════════════════ -->
        <!-- COLUMNA DERECHA: Vista Previa y Exportación                   -->
        <!-- ══════════════════════════════════════════════════════════════ -->
        <main class="panel-preview-main">

            <!-- Encabezado con Contador y Botón Exportar QRs a PDF -->
            <div class="preview-top-bar">
                <div class="preview-header-info">
                    <h3 class="preview-result-count" id="texto-resultados">
                        Resultados: <span id="contador-funcionarios">0</span> funcionarios
                    </h3>
                    <span class="preview-badge-status" id="badge-estado-live">
                        <span class="status-dot"></span>
                        <span>En vivo</span>
                    </span>
                </div>

                <button type="button" class="btn-export-pdf" id="btnExportarPdf" title="Generar hoja de impresión PDF con los registros actuales">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Exportar QRs a PDF</span>
                </button>
            </div>

            <!-- Tabla Minimalista de Vista Previa -->
            <div class="table-container-responsive" id="contenedorTablaReportes">
                <table class="table-minimal-ios" id="tablaPreviewReportes">
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

    </div>

</div>

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
