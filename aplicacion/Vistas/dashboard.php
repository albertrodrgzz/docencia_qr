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
    /* ── Estilos específicos de los componentes del Dashboard ── */
    .dashboard-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .dashboard-subtitle {
        color: var(--text-secondary);
        font-size: 0.92rem;
        margin: 0;
    }

    .toolbar-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 16px;
        border-radius: var(--radius-control);
        font-family: inherit;
        font-size: 0.86rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-action-primary {
        background: var(--apple-blue);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
    }

    .btn-action-primary:hover {
        background: var(--apple-blue-hover);
        transform: translateY(-1px);
    }

    .btn-action-secondary {
        background: #ffffff;
        color: var(--apple-blue);
        border: 1px solid rgba(0, 113, 227, 0.22);
        box-shadow: var(--shadow-subtle);
    }

    .btn-action-secondary:hover {
        border-color: var(--apple-blue);
        transform: translateY(-1px);
    }

    /* ── Widgets de Estadísticas (3 Tarjetas) ── */
    .widgets-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .widget-box {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px 22px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .widget-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
    }

    .widget-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .widget-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
    }

    .widget-icon-wrap.blue   { background: linear-gradient(135deg, #0077ed, #0051c6); }
    .widget-icon-wrap.green  { background: linear-gradient(135deg, #34c759, #248a3d); }
    .widget-icon-wrap.purple { background: linear-gradient(135deg, #af52de, #7e2db7); }

    .widget-icon-wrap i {
        font-size: 1.25rem;
    }

    .widget-pill {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 12px;
    }

    .pill-blue   { background: var(--apple-blue-tint); color: var(--apple-blue); }
    .pill-green  { background: var(--apple-green-tint); color: #248a3d; }
    .pill-purple { background: var(--apple-purple-tint); color: var(--apple-purple); }

    .widget-number {
        font-size: 2.25rem;
        font-weight: 800;
        letter-spacing: -0.035em;
        color: var(--text-heading);
        line-height: 1.1;
    }

    .widget-label {
        font-size: 0.88rem;
        font-weight: 500;
        color: var(--text-secondary);
        margin-top: 5px;
    }

    /* ── Tabla Limpia Dentro de .content-card ── */
    .table-header-block {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .table-header-block h3 {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--text-heading);
        margin: 0;
    }

    .clean-table-container {
        width: 100%;
        overflow-x: auto;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .clean-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.90rem;
        text-align: left;
    }

    .clean-table th {
        padding: 14px 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #86868b;
        background-color: #fafafa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        white-space: nowrap;
    }

    .clean-table tbody tr {
        transition: background 0.2s ease;
    }

    .clean-table tbody tr:hover {
        background-color: #f5f5f7;
    }

    .clean-table td {
        padding: 16px 20px;
        color: #1d1d1f;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }

    .clean-table tbody tr:last-child td {
        border-bottom: none;
    }

    .badge-cedula {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.81rem;
        font-weight: 600;
        padding: 3px 8px;
        background: rgba(0, 0, 0, 0.04);
        border-radius: 6px;
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

    .status-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 20px;
        background: var(--apple-green-tint);
        color: #248a3d;
    }

    .status-tag-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--apple-green);
    }

    /* Botón "Ver QR" */
    .btn-view-qr {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 0.79rem;
        font-weight: 600;
        background: var(--apple-blue-tint);
        color: var(--apple-blue);
        border: none;
        cursor: pointer;
        transition: all 0.16s ease;
    }

    .btn-view-qr:hover {
        background: var(--apple-blue);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(0, 113, 227, 0.28);
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

<!-- Barra Superior de Acciones y Subtítulo -->
<div class="dashboard-toolbar">
    <p class="dashboard-subtitle">Monitoreo de emisión de credenciales y registros de funcionarios institucionales.</p>
    
    <div class="toolbar-buttons">
        <button type="button" class="btn-action btn-action-primary" id="btnNuevoRegistro">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Registro</span>
        </button>

        <button type="button" class="btn-action btn-action-secondary" id="btnCargaExcel">
            <i class="fa-solid fa-file-excel"></i>
            <span>Carga Excel</span>
        </button>
    </div>
</div>

<!-- ── 1. Widgets de Estadísticas (3 Tarjetas) ── -->
<div class="widgets-grid">
    
    <div class="widget-box">
        <div class="widget-top">
            <div class="widget-icon-wrap blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <span class="widget-pill pill-blue">Total</span>
        </div>
        <div class="widget-number"><?= number_format($totalFuncionarios) ?></div>
        <div class="widget-label">Total Funcionarios</div>
    </div>

    <div class="widget-box">
        <div class="widget-top">
            <div class="widget-icon-wrap green">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <span class="widget-pill pill-green">Hoy</span>
        </div>
        <div class="widget-number"><?= number_format($registradosHoy) ?></div>
        <div class="widget-label">Registrados Hoy</div>
    </div>

    <div class="widget-box">
        <div class="widget-top">
            <div class="widget-icon-wrap purple">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <span class="widget-pill pill-purple">Emitidos</span>
        </div>
        <div class="widget-number"><?= number_format($totalFuncionarios) ?></div>
        <div class="widget-label">Certificados QR Activos</div>
    </div>

</div>

<!-- ── 2. Tarjeta Flotante Principal (.content-card) con la Tabla de Funcionarios ── -->
<div class="content-card">
    
    <div class="table-header-block">
        <div>
            <h3>Últimos Funcionarios Registrados</h3>
        </div>
    </div>

    <div class="clean-table-container">
        <?php if (empty($ultimosRegistros)): ?>
            <div class="empty-message">
                <p>No hay funcionarios registrados todavía. Utiliza "Nuevo Registro" o "Carga Excel".</p>
            </div>
        <?php else: ?>
            <table class="clean-table">
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
                    ?>
                        <tr>
                            <td>
                                <span class="badge-cedula"><?= $cedula ?: '—' ?></span>
                            </td>
                            <td>
                                <span class="cell-title"><?= htmlspecialchars((string)($funcionario['nombre_completo'] ?? '—')) ?></span>
                                <span class="cell-subtitle"><?= htmlspecialchars((string)($funcionario['cargo'] ?? '—')) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars((string)($funcionario['departamento'] ?? '—')) ?>
                            </td>
                            <td>
                                <?= !empty($funcionario['fecha_registro']) ? date('d/m/Y h:i A', strtotime((string)$funcionario['fecha_registro'])) : 'Reciente' ?>
                            </td>
                            <td>
                                <span class="status-tag">
                                    <span class="status-tag-dot"></span>
                                    QR Emitido
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <!-- Botón "Ver QR" -->
                                <button 
                                    type="button" 
                                    class="btn-view-qr" 
                                    onclick="verFuncionario('<?= $cedula ?>')"
                                    title="Ver código QR y datos"
                                >
                                    <i class="fa-solid fa-eye"></i>
                                    <span>Ver QR</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<!-- ── Modales de Apoyo (Nuevo Registro y Carga Excel) ── -->
<div class="modal-overlay" id="modalRegistro" role="dialog" aria-modal="true" aria-hidden="true" style="position:fixed;inset:0;background:rgba(0,0,0,0.35);backdrop-filter:blur(16px);display:none;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 28px 60px rgba(0,0,0,0.16);overflow:hidden;">
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

<div class="modal-overlay" id="modalExcel" role="dialog" aria-modal="true" aria-hidden="true" style="position:fixed;inset:0;background:rgba(0,0,0,0.35);backdrop-filter:blur(16px);display:none;align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:24px;width:100%;max-width:460px;box-shadow:0 28px 60px rgba(0,0,0,0.16);overflow:hidden;">
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

</div> <!-- /.content-wrapper -->

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
})();
</script>
