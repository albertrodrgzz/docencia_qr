<?php
/**
 * Vista de Respaldos de Base de Datos — ISPEB Sistema Institucional
 * Ecosistema macOS / iOS con UI de Alta Gama, Micro-interacciones y Calidad Premium
 *
 * @var string|null $tituloVista       Título para topbar y <title>
 * @var bool|null   $mostrarBotonAtras Controla el botón circular de regresar
 */

$tituloVista       = 'Seguridad y Respaldos';
$mostrarBotonAtras = true;

require_once __DIR__ . '/layout/header.php';
?>

<style>
    /* ── Tipografía y Contenedor Centralizado Estilo macOS / Apple ── */
    .backup-container {
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
        padding: 10px 0 30px 0;
    }

    /* ── Tarjeta Principal de Configuración Apple (.backup-card) ── */
    .backup-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03), 0 1px 3px rgba(0, 0, 0, 0.02);
        overflow: hidden;
        animation: fadeUpSmooth 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* ── Sección Superior (Hero) ── */
    .backup-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 52px 40px 36px 40px;
    }

    /* Ícono Gigante con Fondo Circular Celeste Muy Claro */
    .backup-icon-wrapper {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background: rgba(0, 113, 227, 0.08);
        color: var(--apple-blue, #0071e3);
        font-size: 2.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        box-shadow: 0 8px 24px rgba(0, 113, 227, 0.12);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.3s ease;
    }

    .backup-icon-wrapper:hover {
        transform: scale(1.05) translateY(-2px);
        background: rgba(0, 113, 227, 0.12);
    }

    .backup-title {
        font-size: 1.85rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #1d1d1f;
        margin: 0 0 12px 0;
    }

    .backup-subtitle {
        font-size: 0.98rem;
        color: #86868b;
        line-height: 1.55;
        max-width: 580px;
        margin: 0 0 32px 0;
        letter-spacing: -0.01em;
    }

    /* Píldoras de Información de Alta Gama */
    .backup-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        width: 100%;
        max-width: 620px;
        margin-bottom: 8px;
    }

    .backup-info-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: #fbfbfd;
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 14px;
        text-align: left;
        transition: transform 0.2s ease, border-color 0.2s ease;
    }

    .backup-info-pill:hover {
        transform: translateY(-1px);
        border-color: rgba(0, 0, 0, 0.08);
    }

    .backup-pill-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(0, 113, 227, 0.08);
        color: var(--apple-blue, #0071e3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .backup-pill-text strong {
        display: block;
        font-size: 0.84rem;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.01em;
    }

    .backup-pill-text span {
        display: block;
        font-size: 0.74rem;
        color: #86868b;
    }

    /* ── Sección Inferior (Acción) ── */
    .backup-action-box {
        background: #f5f5f7;
        border-top: 1px solid rgba(0, 0, 0, 0.04);
        padding: 34px 40px;
        border-radius: 0 0 24px 24px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
    }

    /* Botón Principal Azul Apple */
    .btn-backup-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        max-width: 380px;
        height: 52px;
        background: linear-gradient(135deg, #0071e3 0%, #0051c6 100%);
        color: #ffffff;
        font-family: inherit;
        font-size: 1.02rem;
        font-weight: 700;
        letter-spacing: -0.015em;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 8px 22px rgba(0, 113, 227, 0.28);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-backup-action:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(0, 113, 227, 0.38);
        color: #ffffff;
    }

    .btn-backup-action:active:not(:disabled) {
        transform: scale(0.99);
    }

    .btn-backup-action:disabled {
        opacity: 0.75;
        cursor: not-allowed;
        transform: none;
        box-shadow: 0 4px 14px rgba(0, 113, 227, 0.18);
    }

    /* Nota de Confianza Institucional */
    .backup-security-note {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 0.78rem;
        color: #86868b;
        letter-spacing: -0.005em;
    }

    .backup-security-note i {
        color: #34c759;
        font-size: 0.85rem;
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <div class="backup-container">

        <!-- ── Tarjeta Centralizada de Configuración (.backup-card) ── -->
        <div class="backup-card">

            <!-- ── Tarea 1: Sección Superior (Hero) ── -->
            <div class="backup-hero">
                
                <!-- Ícono de FontAwesome Grande con Fondo Circular Celeste Claro -->
                <div class="backup-icon-wrapper" aria-hidden="true">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                </div>

                <!-- Título y Subtítulo Explicativo -->
                <h1 class="backup-title">Respaldo Completo del Sistema</h1>
                <p class="backup-subtitle">
                    Esto generará y descargará un archivo estructurado <strong>.sql</strong> con toda la base de datos institucional: funcionarios registrados, credenciales QR emitidas, usuarios administrativos y configuraciones del sistema.
                </p>

                <!-- Cuadrícula de Especificaciones Técnicas y Cobertura -->
                <div class="backup-info-grid">
                    <div class="backup-info-pill">
                        <div class="backup-pill-icon">
                            <i class="fa-solid fa-database"></i>
                        </div>
                        <div class="backup-pill-text">
                            <strong>Estructura y Datos</strong>
                            <span>Volcado integral DDL + DML</span>
                        </div>
                    </div>

                    <div class="backup-info-pill">
                        <div class="backup-pill-icon">
                            <i class="fa-solid fa-qrcode"></i>
                        </div>
                        <div class="backup-pill-text">
                            <strong>Credenciales QR</strong>
                            <span>Tokens y metadatos de emisión</span>
                        </div>
                    </div>

                    <div class="backup-info-pill">
                        <div class="backup-pill-icon">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <div class="backup-pill-text">
                            <strong>Usuarios y Roles</strong>
                            <span>Accesos y permisos del ISPEB</span>
                        </div>
                    </div>

                    <div class="backup-info-pill">
                        <div class="backup-pill-icon">
                            <i class="fa-solid fa-file-code"></i>
                        </div>
                        <div class="backup-pill-text">
                            <strong>Formato Estándar</strong>
                            <span>Compatible con MySQL / MariaDB</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Tarea 1: Sección Inferior (Acción en Contenedor Gris #f5f5f7) ── -->
            <div class="backup-action-box">

                <!-- Botón Principal "Generar Respaldo Ahora" -->
                <button type="button" class="btn-backup-action" id="btnGenerarRespaldo" title="Generar y descargar archivo SQL ahora">
                    <i class="fa-solid fa-arrow-down-to-bracket" aria-hidden="true"></i>
                    <span>Generar Respaldo Ahora</span>
                </button>

                <!-- Nota Institucional de Seguridad -->
                <div class="backup-security-note">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Acción protegida con privilegios de Administrador Institucional ISPEB</span>
                </div>

            </div>

        </div>

    </div>

</div> <!-- /.content-wrapper -->

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- Tarea 2: Micro-interacciones JavaScript                                   -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnRespaldo = document.getElementById('btnGenerarRespaldo');

    btnRespaldo?.addEventListener('click', function () {
        // Guardar contenido original del botón
        const htmlOriginal = btnRespaldo.innerHTML;

        // Cambiar estado a cargando
        btnRespaldo.disabled = true;
        btnRespaldo.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            <span>Generando archivo...</span>
        `;

        // Notificación Toast inicial (liviana, sin backdrop)
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'info',
                title: 'Preparando volcado SQL de la base de datos...'
            });
        }

        // Desencadenar la descarga del archivo SQL
        window.location.href = BASE_URL + '/respaldos/descargar';

        // Restaurar estado del botón tras unos segundos y confirmar éxito
        setTimeout(function () {
            btnRespaldo.disabled = false;
            btnRespaldo.innerHTML = htmlOriginal;

            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: 'Respaldo generado con éxito'
                });
            }
        }, 2600);
    });
});
</script>
