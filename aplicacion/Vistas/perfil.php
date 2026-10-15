<?php
/**
 * Vista: Mi Perfil — ISPEB Sistema Institucional
 * Ecosistema Neo-Corporate con Contenedor Centralizado (700px)
 *
 * @var string|null $tituloVista       Título para topbar y <title>
 * @var bool|null   $mostrarBotonAtras Controla el botón de regresar
 */

$tituloVista       = 'Mi Perfil';
$mostrarBotonAtras = true;

require_once __DIR__ . '/layout/header.php';

$nombreSesion   = $_SESSION['nombre'] ?? (!empty($_SESSION['correo']) ? explode('@', $_SESSION['correo'])[0] : 'admin');
$nombreUsuario  = htmlspecialchars((string) $nombreSesion, ENT_QUOTES, 'UTF-8');
$correoUsuario  = htmlspecialchars((string) ($_SESSION['correo'] ?? 'usuario@ispeb.gob.ve'), ENT_QUOTES, 'UTF-8');
$rolUsuario     = htmlspecialchars((string) ($_SESSION['rol'] ?? 'Administrador'), ENT_QUOTES, 'UTF-8');
$esAdmin        = in_array(strtolower((string)($_SESSION['rol'] ?? '')), ['admin', 'administrador'], true);
$badgeIniciales = strtoupper(substr($nombreUsuario, 0, 2)) ?: 'AD';
?>

<style>
    /* ── Contenedor Centralizado de Máximo 700px ── */
    .profile-central-container {
        max-width: 700px;
        margin: 0 auto;
        padding-bottom: 40px;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ── 1. Tarjeta Superior Flotante (Hero Perfil) ── */
    .profile-hero-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 40px 32px;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        overflow: hidden;
    }

    /* Avatar Gigante en Círculo Perfecto con Sombra */
    .profile-avatar-giant {
        width: 104px;
        height: 104px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3730a3 0%, #1e1b4b 100%);
        color: #ffffff;
        font-size: 2.3rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        border: 4px solid #ffffff;
        box-shadow: 0 12px 28px rgba(55, 48, 163, 0.28);
        user-select: none;
    }

    .profile-hero-name {
        font-size: 1.70rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        color: #1e1b4b;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }

    /* Píldora de Rol en Azul Translúcido */
    .profile-role-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 18px;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        background: rgba(37, 99, 235, 0.08);
        color: #2563eb;
        border: 1px solid rgba(37, 99, 235, 0.18);
    }

    .profile-role-pill .role-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #2563eb;
    }

    /* ── 2. Tarjetas de Secciones (.profile-section-card) ── */
    .profile-section-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 32px;
        box-sizing: border-box;
    }

    .section-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .section-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: rgba(55, 48, 163, 0.08);
        color: #3730a3;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .section-icon-badge.security-badge {
        background: rgba(16, 185, 129, 0.08);
        color: #059669;
    }

    .section-header-titles h2 {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #1e1b4b;
        margin: 0;
    }

    .section-header-titles p {
        font-size: 0.78rem;
        color: #64748b;
        margin: 2px 0 0 0;
    }

    /* ── Formulario y Campos (Inputs Bloqueados y Editables) ── */
    .profile-fields-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .field-label-caps {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .field-input-box {
        position: relative;
    }

    .field-input-box i.input-icon-left {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.90rem;
        pointer-events: none;
    }

    /* Inputs Bloqueados (Solo Lectura, Fondo Gris, Sin Bordes Oscuros) */
    .input-readonly-neo {
        width: 100%;
        height: 44px;
        padding: 0 16px 0 40px;
        background: #f3f4f6;
        border: 1px solid transparent;
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.90rem;
        color: #1e293b;
        font-weight: 500;
        box-sizing: border-box;
        outline: none;
        cursor: default;
    }

    .input-readonly-neo.mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.86rem;
    }

    /* Inputs Editables (Contraseñas) */
    .input-editable-neo {
        width: 100%;
        height: 44px;
        padding: 0 16px 0 40px;
        background: #f8fafc;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        font-family: inherit;
        font-size: 0.90rem;
        color: #1e293b;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .input-editable-neo:focus {
        background: #ffffff;
        border-color: rgba(55, 48, 163, 0.30);
        box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.10);
    }

    /* Botón Índigo "Actualizar Credenciales" */
    .btn-update-indigo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 48px;
        margin-top: 8px;
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
        box-shadow: 0 10px 25px rgba(55, 48, 163, 0.30);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-update-indigo:hover:not(:disabled) {
        background: #4338ca;
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(55, 48, 163, 0.40);
        color: #ffffff;
    }

    .btn-update-indigo:active:not(:disabled) {
        transform: scale(0.98);
    }

    .btn-update-indigo:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Barra Inferior con Estado y Botón Cerrar Sesión */
    .profile-bottom-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 0 4px;
    }

    .status-badge-active {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #059669;
    }

    .btn-logout-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.84rem;
        font-weight: 600;
        color: #dc2626;
        background: rgba(220, 38, 38, 0.08);
        border: 1px solid rgba(220, 38, 38, 0.15);
        transition: all 0.16s ease;
    }

    .btn-logout-link:hover {
        background: rgba(220, 38, 38, 0.14);
        color: #b91c1c;
        transform: translateY(-1px);
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <!-- ── Contenedor Centralizado (max-width: 700px) ── -->
    <div class="profile-central-container">

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- 1. Tarjeta Superior Flotante: Avatar, Nombre y Rol         -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="profile-hero-card">
            <div class="profile-avatar-giant" aria-hidden="true">
                <?= $badgeIniciales ?>
            </div>

            <h1 class="profile-hero-name"><?= $nombreUsuario ?></h1>

            <span class="profile-role-pill">
                <span class="role-dot" aria-hidden="true"></span>
                <span><?= $rolUsuario ?></span>
            </span>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- 2. Sección: Datos Institucionales (Inputs Bloqueados)     -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="profile-section-card">
            <div class="section-card-header">
                <div class="section-icon-badge" aria-hidden="true">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="section-header-titles">
                    <h2>Datos Institucionales</h2>
                    <p>Información de adscripción y acreditación oficial</p>
                </div>
            </div>

            <div class="profile-fields-grid">
                <!-- Campo: Correo Institucional -->
                <div class="field-group">
                    <label class="field-label-caps" for="perfil_correo">
                        <span>Correo Electrónico Institucional</span>
                    </label>
                    <div class="field-input-box">
                        <i class="fa-solid fa-envelope input-icon-left" aria-hidden="true"></i>
                        <input 
                            type="email" 
                            id="perfil_correo" 
                            class="input-readonly-neo mono" 
                            value="<?= $correoUsuario ?>" 
                            readonly 
                            aria-label="Correo institucional"
                        >
                    </div>
                </div>

                <!-- Campo: Departamento / Unidad -->
                <div class="field-group">
                    <label class="field-label-caps" for="perfil_departamento">
                        <span>Departamento / Unidad de Adscripción</span>
                    </label>
                    <div class="field-input-box">
                        <i class="fa-solid fa-building-columns input-icon-left" aria-hidden="true"></i>
                        <input 
                            type="text" 
                            id="perfil_departamento" 
                            class="input-readonly-neo" 
                            value="Dirección de Docencia y Gestión Curricular" 
                            readonly 
                            aria-label="Departamento institucional"
                        >
                    </div>
                </div>

                <!-- Campo: Nivel de Privilegios RBAC -->
                <div class="field-group">
                    <label class="field-label-caps" for="perfil_privilegios">
                        <span>Privilegios del Sistema (RBAC)</span>
                    </label>
                    <div class="field-input-box">
                        <i class="fa-solid fa-shield-halved input-icon-left" aria-hidden="true"></i>
                        <input 
                            type="text" 
                            id="perfil_privilegios" 
                            class="input-readonly-neo" 
                            value="<?= $esAdmin ? 'Acceso Administrativo Completo (Gestión, Respaldos y Reportes)' : 'Acceso Operativo Estándar (Lectura y Emisión QR)' ?>" 
                            readonly 
                            aria-label="Nivel de privilegios"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- 3. Sección: Seguridad (Cambiar Contraseña)                 -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="profile-section-card">
            <div class="section-card-header">
                <div class="section-icon-badge security-badge" aria-hidden="true">
                    <i class="fa-solid fa-key"></i>
                </div>
                <div class="section-header-titles">
                    <h2>Seguridad de la Cuenta</h2>
                    <p>Actualiza tus credenciales periódicamente para proteger tu acceso</p>
                </div>
            </div>

            <form id="formCambiarPassword" autocomplete="off">
                <div class="profile-fields-grid">
                    <!-- Contraseña Actual -->
                    <div class="field-group">
                        <label class="field-label-caps" for="password_actual">Contraseña Actual</label>
                        <div class="field-input-box">
                            <i class="fa-solid fa-lock input-icon-left" aria-hidden="true"></i>
                            <input 
                                type="password" 
                                id="password_actual" 
                                name="password_actual" 
                                class="input-editable-neo" 
                                placeholder="Ingrese su contraseña vigente"
                                required
                            >
                        </div>
                    </div>

                    <!-- Nueva Contraseña -->
                    <div class="field-group">
                        <label class="field-label-caps" for="password_nueva">Nueva Contraseña</label>
                        <div class="field-input-box">
                            <i class="fa-solid fa-lock-open input-icon-left" aria-hidden="true"></i>
                            <input 
                                type="password" 
                                id="password_nueva" 
                                name="password_nueva" 
                                class="input-editable-neo" 
                                placeholder="Mínimo 6 caracteres alfanuméricos"
                                minlength="6"
                                required
                            >
                        </div>
                    </div>

                    <!-- Botón Índigo de Actualización -->
                    <button type="submit" class="btn-update-indigo" id="btnActualizarPass">
                        <i class="fa-solid fa-shield-check" aria-hidden="true"></i>
                        <span>Actualizar Credenciales</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ══════════════════════════════════════════════════════════ -->
        <!-- 4. Acciones Inferiores (Estado y Cerrar Sesión)           -->
        <!-- ══════════════════════════════════════════════════════════ -->
        <div class="profile-bottom-actions">
            <div class="status-badge-active">
                <i class="fa-solid fa-circle-check"></i>
                <span>Sesión institucional verificada y activa</span>
            </div>

            <a href="javascript:void(0)" onclick="confirmarSalida(event)" class="btn-logout-link" title="Finalizar sesión">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>

    </div> <!-- /.profile-central-container -->

</div> <!-- /.content-wrapper -->

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- Micro-interacción: Cambio de Contraseña vía AJAX                          -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formPass     = document.getElementById('formCambiarPassword');
    const btnSubmit    = document.getElementById('btnActualizarPass');
    const passActual   = document.getElementById('password_actual');
    const passNueva    = document.getElementById('password_nueva');

    formPass?.addEventListener('submit', function (e) {
        e.preventDefault();

        const actualVal = passActual.value.trim();
        const nuevaVal  = passNueva.value.trim();

        if (!actualVal || !nuevaVal) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Incompletos',
                    text: 'Debe ingresar la contraseña actual y la nueva contraseña.',
                    confirmButtonColor: '#3730a3'
                });
            } else {
                alert('Debe ingresar la contraseña actual y la nueva contraseña.');
            }
            return;
        }

        if (nuevaVal.length < 6) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Contraseña Débil',
                    text: 'La nueva contraseña debe contener al menos 6 caracteres.',
                    confirmButtonColor: '#3730a3'
                });
            } else {
                alert('La nueva contraseña debe contener al menos 6 caracteres.');
            }
            return;
        }

        const htmlOriginal = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i>
            <span>Actualizando credenciales...</span>
        `;

        const formData = new FormData(formPass);

        fetch(BASE_URL + '/perfil/actualizar-password', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = htmlOriginal;

            if (status === 200 && (body.estado === 'ok' || body.exito)) {
                formPass.reset();

                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'success',
                        title: body.mensaje || 'Contraseña actualizada con éxito.'
                    });
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Credenciales Actualizadas',
                        text: body.mensaje || 'Contraseña institucional modificada con éxito.',
                        confirmButtonColor: '#3730a3'
                    });
                }
            } else {
                const errorMsg = body.mensaje || 'No se pudieron actualizar las credenciales.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Actualización',
                        text: errorMsg,
                        confirmButtonColor: '#dc2626'
                    });
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = htmlOriginal;
            console.error('[Perfil Pass Error]', err);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Red',
                    text: 'Ocurrió un problema de comunicación con el servidor.',
                    confirmButtonColor: '#dc2626'
                });
            } else {
                alert('Ocurrió un problema de comunicación con el servidor.');
            }
        });
    });
});
</script>
