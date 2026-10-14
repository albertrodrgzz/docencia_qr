<?php
/**
 * Vista de Gestión de Usuarios — ISPEB Sistema Institucional de Certificaciones
 * Estandarizada con plantillas maestras (Layout Header + Footer)
 *
 * @var array<int, array<string, mixed>> $usuarios
 */

$tituloVista       = 'Gestión de Usuarios';
$mostrarBotonAtras = true;

require_once __DIR__ . '/layout/header.php';

$listaUsuarios = (array) ($usuarios ?? []);
?>

<style>
    /* ── Estilos específicos de la vista Usuarios ── */
    .users-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .users-toolbar-desc {
        font-size: 0.90rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .btn-add-user {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 18px;
        border-radius: var(--radius-control);
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        background: var(--apple-blue);
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .btn-add-user:hover {
        background: var(--apple-blue-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 113, 227, 0.32);
    }

    .btn-add-user svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
        stroke-width: 2.2;
        fill: none;
    }

    /* Botón Editar Usuario (Estilo Apple Sutil) */
    .btn-editar-usuario {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        background: rgba(0, 0, 0, 0.04);
        color: var(--text-heading);
        font-family: inherit;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid rgba(0, 0, 0, 0.05);
        cursor: pointer;
        transition: all 0.16s ease;
    }

    .btn-editar-usuario:hover {
        background: #0071e3;
        color: #ffffff;
        border-color: #0071e3;
        box-shadow: 0 2px 8px rgba(0, 113, 227, 0.25);
    }

    /* Tabla Estilizada Estilo iOS */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.90rem;
        text-align: left;
    }

    .users-table th {
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

    .users-table tbody tr {
        transition: background 0.2s ease;
    }

    .users-table tbody tr:hover {
        background-color: #f5f5f7;
    }

    .users-table td {
        padding: 16px 20px;
        color: #1d1d1f;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }

    .users-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-avatar-tag {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
        color: #ffffff;
        font-size: 0.74rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
        vertical-align: middle;
    }

    .user-name-title {
        font-weight: 600;
        color: var(--text-heading);
    }

    .user-email-text {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.82rem;
        color: var(--text-secondary);
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .role-badge.admin {
        background: var(--apple-purple-tint);
        color: var(--apple-purple);
    }

    .role-badge.estandar {
        background: var(--apple-blue-tint);
        color: var(--apple-blue);
    }

    .role-dot-sq {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.74rem;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 12px;
        background: var(--apple-green-tint);
        color: #248a3d;
    }

    /* Modal estilo iOS */
    .modal-backdrop-ios {
        position: fixed;
        inset: 0;
        z-index: 999;
        background: rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-card-ios {
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(30px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        border-radius: 24px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 28px 60px rgba(0, 0, 0, 0.16);
        overflow: hidden;
    }

    .modal-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-card-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .modal-close-icon {
        background: rgba(0, 0, 0, 0.05);
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-secondary);
    }

    .modal-form-body {
        padding: 22px 24px;
    }

    .field-row {
        margin-bottom: 15px;
    }

    .field-label-text {
        display: block;
        font-size: 0.77rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 6px;
    }

    .field-input-box {
        width: 100%;
        height: 42px;
        padding: 0 14px;
        font-family: inherit;
        font-size: 0.90rem;
        background: rgba(0, 0, 0, 0.035);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 11px;
        outline: none;
        transition: all 0.2s ease;
    }

    .field-input-box:focus {
        background: #ffffff;
        border-color: var(--apple-blue);
        box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.16);
    }

    .role-grid-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 4px;
    }

    .role-card-opt {
        border: 1.5px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.18s ease;
        background: rgba(0, 0, 0, 0.02);
    }

    .role-card-opt input {
        display: none;
    }

    .role-card-opt.selected {
        background: rgba(0, 113, 227, 0.06);
        border-color: var(--apple-blue);
    }

    .role-card-opt .opt-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-heading);
        display: block;
    }

    .role-card-opt .opt-desc {
        font-size: 0.70rem;
        color: var(--text-secondary);
        display: block;
        margin-top: 2px;
        line-height: 1.25;
    }

    .modal-card-foot {
        padding: 16px 24px 20px;
        border-top: 1px solid var(--border-subtle);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

<!-- ── Contenedor Principal (.content-card) ── -->
<div class="content-card">
    
    <!-- Barra Superior con Botón "Añadir Usuario" a la derecha -->
    <div class="users-toolbar">
        <p class="users-toolbar-desc">Control de acceso institucional y asignación de permisos del sistema.</p>
        
        <button type="button" class="btn-add-user" id="btnAbrirModalUsuario">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Añadir Usuario</span>
        </button>
    </div>

    <!-- Tabla Estilizada de Usuarios -->
    <div class="table-responsive">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo Electrónico</th>
                    <th>Rol (RBAC)</th>
                    <th>Estado</th>
                    <th>Fecha de Registro</th>
                    <th style="text-align: right; width: 100px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($listaUsuarios)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                            No hay usuarios registrados en el sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($listaUsuarios as $usr): 
                        $rolVal = strtolower((string)($usr['rol'] ?? 'estandar'));
                        $esRolAdmin = in_array($rolVal, ['admin', 'administrador'], true);
                        $nombreMostrar = trim((string)($usr['nombre'] ?? '')) ?: 'Usuario Institucional';
                        $inicialesUsr = strtoupper(substr($nombreMostrar, 0, 2));
                    ?>
                        <tr>
                            <td>
                                <span class="user-avatar-tag"><?= $inicialesUsr ?></span>
                                <span class="user-name-title"><?= htmlspecialchars($nombreMostrar) ?></span>
                            </td>
                            <td>
                                <span class="user-email-text"><?= htmlspecialchars((string)($usr['correo'] ?? '—')) ?></span>
                            </td>
                            <td>
                                <span class="role-badge <?= $esRolAdmin ? 'admin' : 'estandar' ?>">
                                    <span class="role-dot-sq"></span>
                                    <?= $esRolAdmin ? 'Administrador' : 'Estándar' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge-ok">
                                    <?= htmlspecialchars((string)($usr['estado'] ?? 'Activo')) ?>
                                </span>
                            </td>
                            <td>
                                <?= !empty($usr['fecha_registro']) ? date('d/m/Y h:i A', strtotime((string)$usr['fecha_registro'])) : '—' ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if (isset($_SESSION['rol']) && in_array(strtolower(trim((string)$_SESSION['rol'])), ['admin', 'administrador'], true)): ?>
                                    <button 
                                        type="button" 
                                        class="btn-editar-usuario" 
                                        onclick="editarUsuario(<?= (int)$usr['id_usuario'] ?>)"
                                        title="Editar usuario"
                                    >
                                        <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        <span>Editar</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
</div> <!-- /.table-container -->
</div> <!-- /.content-card -->
</div> <!-- /.content-wrapper -->

<!-- ── Modal Estilo iOS para Crear Usuario ── -->
<div class="modal-backdrop-ios" id="modalCrearUsuario" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios">
        <div class="modal-card-header">
            <h3>Nuevo Usuario del Sistema</h3>
            <button type="button" class="modal-close-icon" id="btnCerrarModalUsr" aria-label="Cerrar">✕</button>
        </div>
        <form id="formUsuarioNuevo" autocomplete="off">
            <div class="modal-form-body">
                <div class="field-row">
                    <label class="field-label-text" for="new_nombre">Nombre Completo</label>
                    <input type="text" id="new_nombre" name="nombre" class="field-input-box" placeholder="Ej: Inspector Roberto Martínez" required>
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="new_correo">Correo Institucional</label>
                    <input type="email" id="new_correo" name="correo" class="field-input-box" placeholder="usuario@ispeb.gob.ve" required>
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="new_password">Contraseña de Acceso</label>
                    <input type="password" id="new_password" name="password" class="field-input-box" placeholder="Mínimo 6 caracteres" minlength="6" required>
                </div>

                <div class="field-row">
                    <label class="field-label-text">Rol de Acceso (RBAC)</label>
                    <div class="role-grid-selector">
                        <label class="role-card-opt selected" id="optCardEstandar">
                            <input type="radio" name="rol" value="estandar" checked>
                            <span class="opt-title">Estándar</span>
                            <span class="opt-desc">Registro y emisión de certificados QR.</span>
                        </label>

                        <label class="role-card-opt" id="optCardAdmin">
                            <input type="radio" name="rol" value="admin">
                            <span class="opt-title">Administrador</span>
                            <span class="opt-desc">Acceso total, usuarios y respaldo BD.</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-card-foot">
                <button type="button" class="btn-cancel" id="btnCancelarModalUsr" style="height:38px;padding:0 16px;border-radius:10px;background:none;border:1px solid rgba(0,0,0,0.1);cursor:pointer;color:#86868b;">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitUsr" style="height:38px;padding:0 18px;border-radius:10px;background:var(--apple-blue);border:none;color:#fff;font-weight:600;cursor:pointer;">Crear Usuario</button>
            </div>
</div> <!-- /.modal-card-ios -->
</div> <!-- /#modalCrearUsuario -->

<!-- ── Modal Estilo iOS para Editar Usuario (HTML Oculto) ── -->
<div class="modal-backdrop-ios" id="modalEditarUsuario" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios">
        <div class="modal-card-header">
            <h3>Editar Usuario del Sistema</h3>
            <button type="button" class="modal-close-icon" id="btnCerrarModalEditUsr" aria-label="Cerrar">✕</button>
        </div>
        <form id="formUsuarioEditar" autocomplete="off">
            <input type="hidden" id="edit_usr_id" name="id_usuario">
            <div class="modal-form-body">
                <div class="field-row">
                    <label class="field-label-text" for="edit_usr_nombre">Nombre Completo</label>
                    <input type="text" id="edit_usr_nombre" name="nombre" class="field-input-box" placeholder="Ej: Inspector Roberto Martínez" required>
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="edit_usr_correo">Correo Institucional</label>
                    <input type="email" id="edit_usr_correo" name="correo" class="field-input-box" placeholder="usuario@ispeb.gob.ve" required>
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="edit_usr_password">Nueva Contraseña (Opcional)</label>
                    <input type="password" id="edit_usr_password" name="password" class="field-input-box" placeholder="Dejar en blanco para mantener la actual">
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="edit_usr_rol">Rol de Acceso (RBAC)</label>
                    <select id="edit_usr_rol" name="rol" class="field-input-box" style="cursor: pointer;">
                        <option value="estandar">Estándar (Certificados y validación)</option>
                        <option value="admin">Administrador (Control total del sistema)</option>
                    </select>
                </div>

                <div class="field-row">
                    <label class="field-label-text" for="edit_usr_estado">Estado de Cuenta</label>
                    <select id="edit_usr_estado" name="estado" class="field-input-box" style="cursor: pointer;">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo (Acceso suspendido)</option>
                    </select>
                </div>
            </div>

            <div class="modal-card-foot">
                <button type="button" class="btn-cancel" id="btnCancelarModalEditUsr" style="height:38px;padding:0 16px;border-radius:10px;background:none;border:1px solid rgba(0,0,0,0.1);cursor:pointer;color:#86868b;">Cancelar</button>
                <button type="submit" class="btn-submit" id="btnSubmitEditUsr" style="height:38px;padding:0 18px;border-radius:10px;background:var(--apple-blue);border:none;color:#fff;font-weight:600;cursor:pointer;">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>

<!-- ── Scripts Interactivos del Módulo Usuarios ── -->
<script>
/**
 * Abre el modal de edición y carga la información del usuario vía API.
 * Disponible exclusivamente para rol Administrador.
 * @param {number} id
 */
async function editarUsuario(id) {
    if (!id) return;

    try {
        const response = await fetch(BASE_URL + '/api/usuarios/obtener?id=' + encodeURIComponent(id), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await response.json();

        if (!response.ok || data.estado !== 'ok' || !data.usuario) {
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'error',
                    title: data.mensaje || 'No se pudo obtener el usuario.'
                });
            }
            return;
        }

        const usr = data.usuario;
        document.getElementById('edit_usr_id').value     = usr.id_usuario || '';
        document.getElementById('edit_usr_nombre').value = usr.nombre || '';
        document.getElementById('edit_usr_correo').value = usr.correo || '';
        document.getElementById('edit_usr_password').value = '';
        
        const selRol = document.getElementById('edit_usr_rol');
        if (selRol) {
            selRol.value = (usr.rol === 'admin' || usr.rol === 'administrador') ? 'admin' : 'estandar';
        }

        const selEstado = document.getElementById('edit_usr_estado');
        if (selEstado) {
            selEstado.value = usr.estado || 'Activo';
        }

        const modalEdit = document.getElementById('modalEditarUsuario');
        if (modalEdit) {
            modalEdit.classList.remove('fade-out');
            modalEdit.style.display = 'flex';
            modalEdit.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            document.getElementById('edit_usr_nombre')?.focus();
        }
    } catch (err) {
        console.error('[editarUsuario Error]', err);
        if (typeof Toast !== 'undefined') {
            Toast.fire({
                icon: 'error',
                title: 'Error de red al consultar usuario.'
            });
        }
    }
}

(function () {
    'use strict';

    const modal       = document.getElementById('modalCrearUsuario');
    const btnAbrir    = document.getElementById('btnAbrirModalUsuario');
    const btnCerrar   = document.getElementById('btnCerrarModalUsr');
    const btnCancelar = document.getElementById('btnCancelarModalUsr');
    const form        = document.getElementById('formUsuarioNuevo');
    const btnSubmit   = document.getElementById('btnSubmitUsr');

    const optEstandar = document.getElementById('optCardEstandar');
    const optAdmin    = document.getElementById('optCardAdmin');

    // Manejo visual de opciones de rol
    [optEstandar, optAdmin].forEach(card => {
        card?.addEventListener('click', () => {
            optEstandar.classList.remove('selected');
            optAdmin.classList.remove('selected');
            card.classList.add('selected');
            card.querySelector('input').checked = true;
        });
    });

    function toggleModal(show) {
        if (!modal) return;
        if (show) {
            modal.classList.remove('fade-out');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('fade-out');
            setTimeout(() => {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                modal.classList.remove('fade-out');
                document.body.style.overflow = '';
            }, 240);
        }
    }

    btnAbrir?.addEventListener('click', () => {
        form?.reset();
        optEstandar.classList.add('selected');
        optAdmin.classList.remove('selected');
        optEstandar.querySelector('input').checked = true;
        toggleModal(true);
        document.getElementById('new_nombre')?.focus();
    });

    btnCerrar?.addEventListener('click', () => toggleModal(false));
    btnCancelar?.addEventListener('click', () => toggleModal(false));

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) toggleModal(false);
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            toggleModal(false);
        }
    });

    // Envío del formulario con Fetch API
    form?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const nombre   = document.getElementById('new_nombre').value.trim();
        const correo   = document.getElementById('new_correo').value.trim();
        const password = document.getElementById('new_password').value;
        const rolInput = form.querySelector('input[name="rol"]:checked');
        const rol      = rolInput ? rolInput.value : 'estandar';

        if (!nombre || !correo || !password) return;

        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Creando…';

        try {
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('correo', correo);
            formData.append('password', password);
            formData.append('rol', rol);

            const res = await fetch(BASE_URL + '/usuarios', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();

            if (data.exito) {
                toggleModal(false);
                await Swal.fire({
                    title: '¡Usuario Creado!',
                    text: data.mensaje || 'Usuario registrado exitosamente.',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#0071e3'
                });
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error al registrar',
                    text: data.mensaje || 'No se pudo crear el usuario.',
                    icon: 'error',
                    confirmButtonText: 'Revisar',
                    confirmButtonColor: '#0071e3'
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                title: 'Error de Red',
                text: 'No se pudo comunicar con el servidor.',
                icon: 'error',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0071e3'
            });
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Crear Usuario';
        }
    });

    // ── Lógica de Cierre del Modal de Edición de Usuario ──
    const modalEditUsr   = document.getElementById('modalEditarUsuario');
    const btnCerrarEdit  = document.getElementById('btnCerrarModalEditUsr');
    const btnCancelEdit  = document.getElementById('btnCancelarModalEditUsr');
    const formEditUsr    = document.getElementById('formUsuarioEditar');
    const btnSubmitEdit  = document.getElementById('btnSubmitEditUsr');

    function cerrarModalEdicion() {
        if (!modalEditUsr) return;
        modalEditUsr.classList.add('fade-out');
        setTimeout(() => {
            modalEditUsr.style.display = 'none';
            modalEditUsr.setAttribute('aria-hidden', 'true');
            modalEditUsr.classList.remove('fade-out');
            document.body.style.overflow = '';
        }, 240);
    }

    btnCerrarEdit?.addEventListener('click', cerrarModalEdicion);
    btnCancelEdit?.addEventListener('click', cerrarModalEdicion);

    modalEditUsr?.addEventListener('click', (e) => {
        if (e.target === modalEditUsr) cerrarModalEdicion();
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalEditUsr?.style.display === 'flex') {
            cerrarModalEdicion();
        }
    });

    // ── Submit AJAX de Edición de Usuario ──
    formEditUsr?.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (btnSubmitEdit) {
            btnSubmitEdit.disabled = true;
            btnSubmitEdit.textContent = 'Actualizando...';
        }

        const formData = new FormData(this);

        try {
            const res = await fetch(BASE_URL + '/api/usuarios/actualizar', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await res.json();

            if (btnSubmitEdit) {
                btnSubmitEdit.disabled = false;
                btnSubmitEdit.textContent = 'Guardar Cambios';
            }

            if (!res.ok || data.estado !== 'ok') {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: data.mensaje || 'Error al actualizar usuario.'
                    });
                }
                return;
            }

            cerrarModalEdicion();

            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: data.mensaje || 'Usuario actualizado con éxito.'
                });
            }

            // Actualizar la fila correspondiente en la tabla reactivamente sin recargar
            const idUsr       = String(data.usuario?.id_usuario || formData.get('id_usuario'));
            const nuevoNombre = data.usuario?.nombre || formData.get('nombre');
            const nuevoCorreo = data.usuario?.correo || formData.get('correo');
            const nuevoRol    = data.usuario?.rol || formData.get('rol');
            const nuevoEstado = data.usuario?.estado || formData.get('estado');
            const esAdmin     = (nuevoRol === 'admin' || nuevoRol === 'administrador');

            const filas = document.querySelectorAll('.users-table tbody tr');
            filas.forEach(fila => {
                const btnEdit = fila.querySelector('.btn-editar-usuario');
                if (btnEdit && btnEdit.getAttribute('onclick')?.includes(idUsr)) {
                    // Actualizar nombre e iniciales
                    const avatarTag = fila.querySelector('.user-avatar-tag');
                    const nameTitle = fila.querySelector('.user-name-title');
                    const emailText = fila.querySelector('.user-email-text');
                    const roleBadge = fila.querySelector('.role-badge');
                    const statusTag = fila.querySelector('.status-badge-ok, .status-badge');

                    if (avatarTag) avatarTag.textContent = nuevoNombre.substring(0, 2).toUpperCase();
                    if (nameTitle) nameTitle.textContent = nuevoNombre;
                    if (emailText) emailText.textContent = nuevoCorreo;

                    if (roleBadge) {
                        roleBadge.className = `role-badge ${esAdmin ? 'admin' : 'estandar'}`;
                        roleBadge.innerHTML = `<span class="role-dot-sq"></span> ${esAdmin ? 'Administrador' : 'Estándar'}`;
                    }

                    if (statusTag) {
                        statusTag.textContent = nuevoEstado;
                    }

                    // Micro-interacción: Destello suave de fila actualizada
                    fila.style.transition = 'background-color 0.4s ease';
                    fila.style.backgroundColor = 'rgba(52, 199, 89, 0.15)';
                    setTimeout(() => { fila.style.backgroundColor = ''; }, 1200);
                }
            });

        } catch (err) {
            console.error('[actualizarUsuario Error]', err);
            if (btnSubmitEdit) {
                btnSubmitEdit.disabled = false;
                btnSubmitEdit.textContent = 'Guardar Cambios';
            }
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de red al actualizar usuario.'
                });
            }
        }
    });

})();
</script>
