<?php
$page_title = 'Respaldos';

/**
 * Vista de Respaldos y Restauración de Base de Datos — ISPEB Sistema Institucional
 * Ecosistema Neo-Corporate con Layout Horizontal de Dos Columnas (CSS Grid)
 *
 * @var string|null $tituloVista       Título para topbar y <title>
 * @var bool|null   $mostrarBotonAtras Controla el botón de regresar
 */

$tituloVista       = 'Seguridad y Respaldos';
$mostrarBotonAtras = true;

require_once __DIR__ . '/layout/header.php';
?>

<style>
    /* ── Cabecera Principal Flexible (.page-header-flex) ── */
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        width: 100%;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .page-header-left {
        text-align: left;
        max-width: 680px;
        margin: 0;
    }

    .page-header-left h1 {
        font-size: 1.85rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #1e1b4b;
        margin: 0 0 6px 0;
        text-align: left;
    }

    .page-header-left p {
        font-size: 0.90rem;
        color: #64748b;
        margin: 0;
        line-height: 1.45;
        text-align: left;
    }

    .page-header-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin: 0;
    }

    .trust-pill-respaldo {
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

    .status-dot-emerald {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
    }

    /* ── Cuadrícula Horizontal de Dos Columnas (.respaldos-grid) ── */
    .respaldos-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: stretch;
        width: 100%;
    }

    @media (max-width: 1024px) {
        .respaldos-grid {
            grid-template-columns: 1fr !important;
            gap: 20px;
        }
    }

    /* ── Tarjetas Individuales Neo-Corporate ── */
    .card-respaldo {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-sizing: border-box;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
    }

    .card-respaldo:hover {
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.06);
    }

    .card-respaldo-header {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 20px;
    }

    .card-respaldo-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .icon-export {
        background: rgba(55, 48, 163, 0.08);
        color: #3730a3;
    }

    .icon-restore {
        background: rgba(220, 38, 38, 0.08);
        color: #dc2626;
    }

    .card-respaldo-texts h2 {
        font-size: 1.30rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1e1b4b;
        margin: 0 0 6px 0;
    }

    .card-respaldo-texts p {
        font-size: 0.88rem;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    /* ── Tarjeta Izquierda: Lista de Píldoras Informativas ── */
    .backup-pills-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 20px 0 28px 0;
    }

    .backup-item-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.03);
        border-radius: 14px;
        transition: background 0.18s ease;
    }

    .backup-item-pill:hover {
        background: #f1f5f9;
    }

    .backup-item-bullet {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #ffffff;
        color: #3730a3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        flex-shrink: 0;
    }

    .backup-item-content strong {
        display: block;
        font-size: 0.86rem;
        font-weight: 600;
        color: #1e293b;
    }

    .backup-item-content span {
        display: block;
        font-size: 0.76rem;
        color: #64748b;
    }

    /* ── Tarjeta Derecha: Área de Drag & Drop y Archivo ── */
    .restore-dropzone {
        background: #f3f4f6;
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        margin: 20px 0 28px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .restore-dropzone:hover,
    .restore-dropzone.dragover {
        border-color: #3730a3;
        background: #eef2ff;
        transform: translateY(-1px);
    }

    .dropzone-icon {
        font-size: 2rem;
        color: #64748b;
        margin-bottom: 4px;
        transition: color 0.2s ease;
    }

    .restore-dropzone:hover .dropzone-icon,
    .restore-dropzone.dragover .dropzone-icon {
        color: #3730a3;
    }

    .dropzone-label {
        font-size: 0.90rem;
        font-weight: 600;
        color: #1e293b;
    }

    .dropzone-hint {
        font-size: 0.76rem;
        color: #64748b;
    }

    .file-chosen-box {
        display: none;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        box-sizing: border-box;
        padding: 10px 14px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        margin-top: 10px;
        font-size: 0.84rem;
        color: #1e1b4b;
        font-weight: 600;
    }

    .file-chosen-info {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-chosen-info i {
        color: #3730a3;
    }

    .btn-remove-file {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 0.88rem;
        padding: 2px 6px;
        border-radius: 6px;
        transition: color 0.16s ease;
    }

    .btn-remove-file:hover {
        color: #dc2626;
    }

    /* ── Botones Principales de Acción ── */
    .btn-export-indigo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 48px;
        background: #3730a3;
        color: #ffffff;
        font-family: inherit;
        font-size: 0.96rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 10px 25px rgba(55, 48, 163, 0.32);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-export-indigo:hover:not(:disabled) {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(55, 48, 163, 0.40);
        color: #ffffff;
    }

    .btn-export-indigo:active:not(:disabled) {
        transform: scale(0.98);
    }

    .btn-export-indigo:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .btn-restore-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 48px;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-family: inherit;
        font-size: 0.96rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        border-radius: 14px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-restore-danger:hover:not(:disabled) {
        background: #fee2e2;
        border-color: #f87171;
        color: #b91c1c;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.15);
    }

    .btn-restore-danger:active:not(:disabled) {
        transform: scale(0.98);
    }

    .btn-restore-danger:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .card-footer-note {
        font-size: 0.74rem;
        color: #94a3b8;
        text-align: center;
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <!-- ── Cabecera Principal Flexible (.page-header-flex) ── -->
    <div class="page-header-flex">
        <div class="page-header-left">
            <h1>Seguridad y Respaldos</h1>
            <p>Gestiona copias de seguridad íntegras de la base de datos institucional y restaura puntos de control.</p>
        </div>
        <div class="page-header-right">
            <div class="trust-pill-respaldo" title="Motor de base de datos MySQL / MariaDB activo">
                <span class="status-dot-emerald"></span>
                <span>Motor SQL Activo</span>
            </div>
            <div class="trust-pill-respaldo" title="Acceso exclusivo para rol de Administrador">
                <i class="fa-solid fa-lock" style="color: #64748b;"></i>
                <span>Acceso Admin</span>
            </div>
        </div>
    </div>

    <!-- ── Tarea 1: Cuadrícula Horizontal de Dos Columnas (.respaldos-grid) ── -->
    <div class="respaldos-grid">

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- TARJETA IZQUIERDA: Generar / Exportar Respaldo            -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="card-respaldo">
            <div>
                <div class="card-respaldo-header">
                    <div class="card-respaldo-icon icon-export" aria-hidden="true">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div class="card-respaldo-texts">
                        <h2>Exportar Base de Datos</h2>
                        <p>Genera un archivo .sql con la estructura y datos actuales.</p>
                    </div>
                </div>

                <!-- Píldoras Informativas en Lista Limpia -->
                <div class="backup-pills-list">
                    <div class="backup-item-pill">
                        <div class="backup-item-bullet" aria-hidden="true">
                            <i class="fa-solid fa-table-list"></i>
                        </div>
                        <div class="backup-item-content">
                            <strong>Estructura y Datos</strong>
                            <span>Volcado integral de tablas DDL + DML</span>
                        </div>
                    </div>

                    <div class="backup-item-pill">
                        <div class="backup-item-bullet" aria-hidden="true">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div class="backup-item-content">
                            <strong>Usuarios y Roles</strong>
                            <span>Cuentas activas y credenciales institucionales</span>
                        </div>
                    </div>

                    <div class="backup-item-pill">
                        <div class="backup-item-bullet" aria-hidden="true">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div class="backup-item-content">
                            <strong>Funcionarios y QRs</strong>
                            <span>Registros docentes, cargos y tokens emitidos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <!-- Botón Principal "Generar Respaldo" -->
                <button type="button" class="btn-export-indigo" id="btnGenerarRespaldo" title="Descargar copia de seguridad en formato SQL">
                    <i class="fa-solid fa-cloud-arrow-down" aria-hidden="true"></i>
                    <span>Generar Respaldo</span>
                </button>
                <div class="card-footer-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Descarga directa compatible con phpMyAdmin y MySQL CLI</span>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- TARJETA DERECHA: Restaurar Sistema                         -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="card-respaldo">
            <div>
                <div class="card-respaldo-header">
                    <div class="card-respaldo-icon icon-restore" aria-hidden="true">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div class="card-respaldo-texts">
                        <h2>Restaurar Sistema</h2>
                        <p>Sube un archivo .sql previamente generado para restaurar la plataforma.</p>
                    </div>
                </div>

                <!-- Input Oculto de Archivo SQL -->
                <input type="file" id="inputArchivoSql" accept=".sql" style="display: none;">

                <!-- Zona Drag & Drop Estilizada -->
                <div class="restore-dropzone" id="dropzoneSql" title="Haga clic o arrastre el archivo .sql aquí">
                    <i class="fa-solid fa-file-arrow-up dropzone-icon" aria-hidden="true"></i>
                    <span class="dropzone-label">Seleccionar archivo .sql</span>
                    <span class="dropzone-hint">O arrastra y suelta tu archivo de respaldo aquí</span>

                    <!-- Caja de Archivo Seleccionado -->
                    <div class="file-chosen-box" id="cajaArchivoElegido">
                        <div class="file-chosen-info">
                            <i class="fa-solid fa-file-code"></i>
                            <span id="nombreArchivoElegido">archivo.sql</span>
                        </div>
                        <button type="button" class="btn-remove-file" id="btnQuitarArchivo" title="Quitar archivo">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <!-- Botón de Acción Restaurar (Peligro/Precaución) -->
                <button type="button" class="btn-restore-danger" id="btnRestaurarBd" title="Restaurar base de datos a partir del archivo seleccionado">
                    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                    <span>Restaurar Base de Datos</span>
                </button>
                <div class="card-footer-note">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b;"></i>
                    <span>Se requiere confirmación administrativa previa a la ejecución</span>
                </div>
            </div>
        </div>

    </div> <!-- /.respaldos-grid -->

</div> <!-- /.content-wrapper -->

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- Micro-interacciones JavaScript & Confirmación SweetAlert2                 -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnRespaldo         = document.getElementById('btnGenerarRespaldo');
    const btnRestaurar        = document.getElementById('btnRestaurarBd');
    const inputSql            = document.getElementById('inputArchivoSql');
    const dropzone            = document.getElementById('dropzoneSql');
    const cajaArchivoElegido  = document.getElementById('cajaArchivoElegido');
    const nombreArchivoLabel  = document.getElementById('nombreArchivoElegido');
    const btnQuitarArchivo    = document.getElementById('btnQuitarArchivo');

    let archivoSeleccionado   = null;

    // ── 1. Generar Respaldo (Descarga) ──
    btnRespaldo?.addEventListener('click', function () {
        const htmlOriginal = btnRespaldo.innerHTML;

        btnRespaldo.disabled = true;
        btnRespaldo.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            <span>Generando archivo...</span>
        `;

        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'info',
                title: 'Preparando volcado SQL de la base de datos...'
            });
        }

        window.location.href = BASE_URL + '/respaldos/descargar';

        setTimeout(function () {
            btnRespaldo.disabled = false;
            btnRespaldo.innerHTML = htmlOriginal;

            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: 'Respaldo generado con éxito'
                });
            }
        }, 2500);
    });

    // ── 2. Manejo de Selección de Archivo SQL (Click y Drag & Drop) ──
    dropzone?.addEventListener('click', function (e) {
        if (e.target.closest('#btnQuitarArchivo')) return;
        inputSql?.click();
    });

    inputSql?.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            establecerArchivo(this.files[0]);
        }
    });

    // Drag and Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone?.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone?.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });
    });

    dropzone?.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            establecerArchivo(dt.files[0]);
        }
    });

    function establecerArchivo(file) {
        if (!file.name.toLowerCase().endsWith('.sql')) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Formato Inválido',
                    text: 'Solo se admiten archivos de respaldo con extensión .sql',
                    confirmButtonColor: '#3730a3'
                });
            }
            return;
        }

        archivoSeleccionado = file;
        nombreArchivoLabel.textContent = file.name;
        cajaArchivoElegido.style.display = 'flex';
    }

    btnQuitarArchivo?.addEventListener('click', function (e) {
        e.stopPropagation();
        archivoSeleccionado = null;
        inputSql.value = '';
        cajaArchivoElegido.style.display = 'none';
    });

    // ── 3. JS Crítico: Restaurar Base de Datos con Alerta Severa ──
    btnRestaurar?.addEventListener('click', function () {
        if (!archivoSeleccionado) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Archivo Requerido',
                    text: 'Por favor seleccione o arrastre un archivo .sql antes de continuar.',
                    confirmButtonColor: '#3730a3'
                });
            } else {
                alert('Por favor seleccione un archivo .sql antes de continuar.');
            }
            return;
        }

        // Alerta de confirmación severa con SweetAlert2 estilo Neo-Corporate Apple
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Restaurar Base de Datos?',
                text: 'Esto sobrescribirá todos los datos actuales. ¿Estás seguro?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, restaurar base de datos',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    popup: 'apple-popup',
                    confirmButton: 'btn-apple-danger',
                    cancelButton: 'btn-apple-cancel'
                },
                backdrop: `rgba(0,0,0,0.4)`
            }).then((result) => {
                if (result.isConfirmed) {
                    ejecutarRestauracion();
                }
            });
        } else {
            if (confirm('Esto sobrescribirá todos los datos actuales. ¿Estás seguro?')) {
                ejecutarRestauracion();
            }
        }
    });

    function ejecutarRestauracion() {
        const formData = new FormData();
        formData.append('archivo_sql', archivoSeleccionado);

        const htmlOriginal = btnRestaurar.innerHTML;
        btnRestaurar.disabled = true;
        btnRestaurar.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            <span>Restaurando datos...</span>
        `;

        fetch(BASE_URL + '/respaldos/restaurar', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            btnRestaurar.disabled = false;
            btnRestaurar.innerHTML = htmlOriginal;

            if (status === 200 && (body.estado === 'ok' || body.exito)) {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'success',
                        title: body.mensaje || 'Base de datos restaurada con éxito.'
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Restauración Exitosa',
                        text: body.mensaje,
                        confirmButtonColor: '#3730a3'
                    });
                }

                // Limpiar selección de archivo
                archivoSeleccionado = null;
                inputSql.value = '';
                cajaArchivoElegido.style.display = 'none';

                // Recargar página después de 2.2 segundos para reflejar los datos frescos
                setTimeout(() => {
                    window.location.reload();
                }, 2200);
            } else {
                const errorMsg = body.mensaje || 'No se pudo completar la restauración de la base de datos.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Restauración',
                        text: errorMsg,
                        confirmButtonColor: '#dc2626'
                    });
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        })
        .catch(err => {
            btnRestaurar.disabled = false;
            btnRestaurar.innerHTML = htmlOriginal;
            console.error('[Restaurar Error]', err);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Fallo de Conexión',
                    text: 'Ocurrió un error al enviar el archivo al servidor.',
                    confirmButtonColor: '#dc2626'
                });
            } else {
                alert('Ocurrió un error al enviar el archivo al servidor.');
            }
        });
    }
});
</script>
