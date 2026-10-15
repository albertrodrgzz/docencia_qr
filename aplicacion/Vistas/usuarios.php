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

if (!function_exists('obtenerIniciales')) {
    function obtenerIniciales(string $nombre): string {
        $partes = array_values(array_filter(explode(' ', trim($nombre))));
        if (empty($partes)) return 'US';
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
    /* ── Estilos Específicos: Gestión de Usuarios (Estilo Neo-Corporate) ── */

    /* ── Cabecera Hero Neo-Corporate ── */
    .users-neo-header {
        margin-bottom: 24px;
    }

    .users-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
    }

    .users-main-title {
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

    .pill-rbac-shield {
        background: #ffffff;
        color: #334155;
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    }

    .text-shield-blue {
        color: #2563eb;
    }

    /* Barra de herramientas interna (Toolbar) */
    .toolbar-users {
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

    /* Botón Añadir Usuario (fondo índigo brillante, texto blanco, sombra sutil) */
    .btn-registro-indigo,
    .btn-add-user {
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
    .btn-add-user:hover {
        background: #4338ca;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(55, 48, 163, 0.35);
    }

    /* ── Tabla de Datos Hiper-Minimalista Edge-to-Edge ── */
    .table-container-responsive,
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.04);
        background: #ffffff;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
    }

    .table-minimal-ios,
    .users-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .table-minimal-ios thead th,
    .users-table th {
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

    .table-minimal-ios tbody tr,
    .users-table tbody tr {
        transition: background 0.18s ease;
    }

    .table-minimal-ios tbody tr:hover,
    .users-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .table-minimal-ios tbody td,
    .users-table td {
        padding: 14px 20px;
        font-size: 0.88rem;
        color: #1e293b;
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        vertical-align: middle;
    }

    .table-minimal-ios tbody tr:last-child td,
    .users-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Avatares Dinámicos & Nombre */
    .user-avatar-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle-sm,
    .user-avatar-tag {
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

    .cell-nombre,
    .user-name-title {
        font-weight: 600;
        color: #1e1b4b;
        letter-spacing: -0.01em;
    }

    /* Correo Institucional */
    .cell-correo,
    .user-email-text {
        font-size: 0.88rem;
        color: #475569;
    }

    /* Píldoras de Rol de Acceso (Pills) */
    .role-pill,
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .pill-role-admin,
    .role-badge.admin {
        background: #e0e7ff;
        color: #3730a3;
    }

    .pill-role-estandar,
    .role-badge.estandar {
        background: #ecfdf5;
        color: #065f46;
    }

    .role-dot-admin {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #3730a3;
    }

    .role-dot-estandar {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
    }

    /* Píldora de Estado: gris claro con punto verde y texto "Activo" */
    .status-pill-active,
    .status-badge-ok {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        background: #f1f5f9;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .status-dot-emerald {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    /* Botón Editar Usuario (texto gris oscuro, lápiz, sin bordes duros) */
    .btn-action-edit,
    .btn-editar-usuario {
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
    .btn-editar-usuario:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #1e1b4b;
        box-shadow: none;
        border: none;
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
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: not-allowed;
        font-size: 0.75rem;
        transition: all 0.2s ease;
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

    .role-card,
    .role-card-opt {
        border: 1.5px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.18s ease;
        background: rgba(0, 0, 0, 0.02);
        user-select: none;
    }

    .role-card:hover,
    .role-card-opt:hover {
        border-color: rgba(0, 113, 227, 0.4);
        background: rgba(0, 113, 227, 0.03);
    }

    .role-card.active,
    .role-card-opt.selected {
        background: rgba(0, 113, 227, 0.08);
        border-color: var(--apple-blue, #0071e3);
        box-shadow: 0 0 0 1px var(--apple-blue, #0071e3);
    }

    .role-card .opt-title,
    .role-card-opt .opt-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-heading);
        display: block;
    }

    .role-card .opt-desc,
    .role-card-opt .opt-desc {
        font-size: 0.70rem;
        color: var(--text-secondary);
        display: block;
        margin-top: 2px;
        line-height: 1.25;
    }

    .btn-apple-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 38px;
        padding: 0 18px;
        border-radius: 10px;
        background: var(--apple-blue, #0071e3);
        border: none;
        color: #ffffff;
        font-family: inherit;
        font-size: 0.88rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.18s ease;
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.25);
    }

    .btn-apple-primary:hover {
        background: #0077ed;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0, 113, 227, 0.35);
    }

    .btn-apple-primary:active {
        transform: scale(0.98);
    }

    .btn-apple-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
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

    <!-- ── Encabezado Neo-Corporate de Gestión de Usuarios ── -->
    <div class="users-neo-header">
        <div class="users-title-row">
            <h1 class="users-main-title">Gestión de Usuarios</h1>
            <div class="trust-pills-group">
                <span class="trust-pill pill-active-count">
                    <?= count($listaUsuarios) ?> Usuarios Activos
                </span>
                <span class="trust-pill pill-rbac-shield">
                    <i class="fa-solid fa-shield-halved text-shield-blue" aria-hidden="true"></i>
                    <span>Control de Acceso RBAC</span>
                </span>
            </div>
        </div>
    </div>

    <!-- ── Contenedor Principal (.content-card) ── -->
    <div class="content-card">
        
        <!-- ── Barra de Herramientas (Toolbar Interno) ── -->
        <div class="toolbar-users">
            <!-- Input de Búsqueda con indicador ⌘K -->
            <div class="search-wrapper-ios">
                <i class="fa-solid fa-magnifying-glass search-icon-svg" aria-hidden="true"></i>
                <input 
                    type="text" 
                    id="inputBuscarUsuario" 
                    class="search-input-ios" 
                    placeholder="Buscar por nombre o correo..." 
                    aria-label="Buscar usuarios"
                    autocomplete="off"
                >
                <kbd class="search-kbd-tag" title="Presiona ⌘K o Ctrl+K para buscar">⌘K</kbd>
            </div>

            <!-- Botón Añadir Usuario (visible solo para rol Administrador) -->
            <?php if (isset($_SESSION['rol']) && in_array(strtolower(trim((string)$_SESSION['rol'])), ['admin', 'administrador'], true)): ?>
                <button type="button" class="btn-registro-indigo btn-add-user" id="btnAbrirModalUsuario" title="Registrar un nuevo usuario">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    <span>Añadir Usuario</span>
                </button>
            <?php endif; ?>
        </div>

        <!-- ── Tabla Estilizada Edge-to-Edge de Usuarios ── -->
        <div class="table-container-responsive table-responsive">
            <table class="table-minimal-ios users-table" id="tablaUsuarios">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Correo Institucional</th>
                        <th>Rol de Acceso</th>
                        <th>Estado</th>
                        <th style="text-align: right; width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaUsuarios)): ?>
                        <tr id="filaVaciaUsuarios">
                            <td colspan="5" style="text-align: center; padding: 48px 20px; color: #86868b;">
                                No hay usuarios registrados en el sistema.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listaUsuarios as $usr): 
                            $rolVal = strtolower((string)($usr['rol'] ?? 'estandar'));
                            $esRolAdmin = in_array($rolVal, ['admin', 'administrador'], true);
                            $nombreMostrar = trim((string)($usr['nombre'] ?? '')) ?: 'Usuario Institucional';
                            $correoMostrar = (string)($usr['correo'] ?? '—');
                            $inicialesUsr = obtenerIniciales($nombreMostrar);
                            $avatarColor = obtenerColorAvatar((string)($usr['id_usuario'] ?? $nombreMostrar));
                        ?>
                            <tr class="fila-usuario" data-filtro="<?= strtolower($nombreMostrar . ' ' . $correoMostrar . ' ' . ($esRolAdmin ? 'admin administrador' : 'estandar')) ?>">
                                <td>
                                    <div class="user-avatar-cell">
                                        <div class="avatar-circle-sm" style="background-color: <?= $avatarColor['bg'] ?>; color: <?= $avatarColor['color'] ?>;">
                                            <?= $inicialesUsr ?>
                                        </div>
                                        <span class="cell-nombre"><?= htmlspecialchars($nombreMostrar) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="cell-correo"><?= htmlspecialchars($correoMostrar) ?></span>
                                </td>
                                <td>
                                    <?php if ($esRolAdmin): ?>
                                        <span class="role-pill pill-role-admin">
                                            <span class="role-dot-admin" aria-hidden="true"></span>
                                            <span>Administrador</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="role-pill pill-role-estandar">
                                            <span class="role-dot-estandar" aria-hidden="true"></span>
                                            <span>Estándar</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-pill-active">
                                        <span class="status-dot-emerald" aria-hidden="true"></span>
                                        <span><?= htmlspecialchars((string)($usr['estado'] ?? 'Activo')) ?></span>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <?php if (isset($_SESSION['rol']) && in_array(strtolower(trim((string)$_SESSION['rol'])), ['admin', 'administrador'], true)): ?>
                                        <button 
                                            type="button" 
                                            class="btn-action-edit btn-editar-usuario" 
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
        </div> <!-- /.table-container-responsive -->

        <!-- ── Paginación Inferior ── -->
        <div class="table-pagination-footer">
            <div class="pagination-info-text">
                Mostrando 1 a <?= count($listaUsuarios) ?> de <?= count($listaUsuarios) ?> usuarios registrados • Total 1 Página
            </div>
            <div class="pagination-controls-group">
                <button type="button" class="btn-pagination-nav" disabled title="Página anterior" aria-label="Anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <span class="page-number-active" aria-current="page">1</span>
                <button type="button" class="btn-pagination-nav" disabled title="Página siguiente" aria-label="Siguiente">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>

    </div> <!-- /.content-card -->
</div> <!-- /.content-wrapper -->

<!-- ── Modal Estilo iOS para Crear Usuario ── -->
<div class="modal-backdrop-ios modal-overlay" id="modalCrearUsuario" data-modal="modal-nuevo-usuario" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios modal-content">
        <div class="modal-card-header">
            <h3>Nuevo Usuario del Sistema</h3>
            <button type="button" class="modal-close-icon" id="btnCerrarModalUsr" aria-label="Cerrar">✕</button>
        </div>
        <form id="form-nuevo-usuario" autocomplete="off">
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
                    <!-- Input oculto para enviar el valor del rol seleccionado mediante las tarjetas visuales -->
                    <input type="hidden" name="rol" id="input-rol" value="estandar" required>
                    <div class="role-grid-selector">
                        <div class="role-card role-card-opt active" data-rol="estandar" id="optCardEstandar">
                            <span class="opt-title">Estándar</span>
                            <span class="opt-desc">Registro y emisión de certificados QR.</span>
                        </div>

                        <div class="role-card role-card-opt" data-rol="admin" id="optCardAdmin">
                            <span class="opt-title">Administrador</span>
                            <span class="opt-desc">Acceso total, usuarios y respaldo BD.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-card-foot">
                <button type="button" class="btn-cancel" id="btnCancelarModalUsr" style="height:38px;padding:0 16px;border-radius:10px;background:none;border:1px solid rgba(0,0,0,0.1);cursor:pointer;color:#86868b;">Cancelar</button>
                <button type="submit" class="btn-apple-primary">Crear Usuario</button>
            </div>
        </form>
    </div> <!-- /.modal-card-ios -->
</div> <!-- /#modalCrearUsuario -->

<!-- ── Modal Estilo iOS para Editar Usuario (HTML Oculto) ── -->
<div class="modal-backdrop-ios modal-overlay" id="modalEditarUsuario" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="modal-card-ios modal-content">
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
    const formNuevo   = document.getElementById('form-nuevo-usuario') || document.getElementById('formUsuarioNuevo');

    const inputBuscar = document.getElementById('inputBuscarUsuario');
    const filasUsuarios = document.querySelectorAll('.fila-usuario');

    // ── Filtrado en tiempo real en la tabla de usuarios ──
    inputBuscar?.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        filasUsuarios.forEach(fila => {
            const contenido = fila.getAttribute('data-filtro') || '';
            fila.style.display = contenido.includes(query) ? '' : 'none';
        });
    });

    // ── Atajo de teclado ⌘K / Ctrl+K para buscar ──
    window.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            inputBuscar?.focus();
            inputBuscar?.select();
        }
    });

    // Función auxiliar para iniciales en JS
    function obtenerInicialesJs(nombre) {
        if (!nombre) return 'US';
        const partes = nombre.trim().split(/\s+/).filter(Boolean);
        if (partes.length === 0) return 'US';
        if (partes.length === 1) return partes[0].substring(0, 2).toUpperCase();
        return (partes[0].substring(0, 1) + partes[1].substring(0, 1)).toUpperCase();
    }

    // ── Función universal para abrir/cerrar modal por ID ──
    function toggleModal(show, modalId = 'modalCrearUsuario') {
        const targetModal = document.getElementById(modalId) 
            || document.querySelector(`[data-modal="${modalId}"]`) 
            || modal;
        if (!targetModal) return;

        if (show) {
            targetModal.classList.remove('fade-out');
            targetModal.style.display = 'flex';
            targetModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        } else {
            targetModal.classList.add('fade-out');
            setTimeout(() => {
                targetModal.style.display = 'none';
                targetModal.setAttribute('aria-hidden', 'true');
                targetModal.classList.remove('fade-out');
                document.body.style.overflow = '';
            }, 240);
        }
    }

    // Función global accesible para cerrar modales
    window.cerrarModal = function (modalId) {
        toggleModal(false, modalId);
    };

    // ── Tarea 2: Lógica JS para las Tarjetas de Rol ──
    const tarjetasRol = document.querySelectorAll('.role-card');
    const inputRol = document.getElementById('input-rol');

    tarjetasRol.forEach(tarjeta => {
        tarjeta.addEventListener('click', function() {
            // Quitar clase activa de todas
            tarjetasRol.forEach(t => t.classList.remove('active'));
            // Poner clase activa a la cliqueada
            this.classList.add('active');
            // Actualizar el valor del input oculto (asume que la tarjeta tiene un data-rol="admin")
            if (inputRol) {
                inputRol.value = this.getAttribute('data-rol') || 'estandar';
            }
        });
    });

    btnAbrir?.addEventListener('click', () => {
        if (formNuevo) {
            formNuevo.reset();
        }
        // Restablecer selección a 'estandar' por defecto
        tarjetasRol.forEach(t => {
            if (t.getAttribute('data-rol') === 'estandar') {
                t.classList.add('active');
            } else {
                t.classList.remove('active');
            }
        });
        if (inputRol) inputRol.value = 'estandar';

        toggleModal(true, 'modalCrearUsuario');
        document.getElementById('new_nombre')?.focus();
    });

    btnCerrar?.addEventListener('click', () => toggleModal(false, 'modalCrearUsuario'));
    btnCancelar?.addEventListener('click', () => toggleModal(false, 'modalCrearUsuario'));

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) toggleModal(false, 'modalCrearUsuario');
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            toggleModal(false, 'modalCrearUsuario');
        }
    });

    // ── Tarea 3: Reconectar la Petición AJAX (#form-nuevo-usuario) ──
    const formSubmitTarget = document.getElementById('form-nuevo-usuario') || formNuevo;
    formSubmitTarget?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Cambiar estado del botón a cargando
        const btnSubmit = this.querySelector('button[type="submit"]');
        const textoOriginal = btnSubmit ? btnSubmit.innerHTML : 'Crear Usuario';
        if (btnSubmit) {
            btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creando...';
            btnSubmit.disabled = true;
        }

        try {
            const formData = new FormData(this);
            const response = await fetch(BASE_URL + '/api/usuarios/guardar', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            const data = await response.json();
            
            if (data.estado === 'ok' || data.exito) {
                if (typeof Toast !== 'undefined') {
                    Toast.fire({ icon: 'success', title: data.mensaje || 'Usuario creado exitosamente' });
                }
                // Cerrar el modal
                cerrarModal('modal-nuevo-usuario');
                this.reset();
                // Opcional: recargar la tabla o inyectar la fila
                setTimeout(() => location.reload(), 1000); 
            } else {
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: data.mensaje || 'No se pudo crear el usuario.', 
                    customClass: { popup: 'apple-popup' } 
                });
            }
        } catch (error) {
            console.error('Error AJAX:', error);
            if (typeof Toast !== 'undefined') {
                Toast.fire({ icon: 'error', title: 'Error de conexión' });
            }
        } finally {
            if (btnSubmit) {
                btnSubmit.innerHTML = textoOriginal;
                btnSubmit.disabled = false;
            }
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
            const nuevoNombre = String(data.usuario?.nombre || formData.get('nombre') || '');
            const nuevoCorreo = String(data.usuario?.correo || formData.get('correo') || '');
            const nuevoRol    = String(data.usuario?.rol || formData.get('rol') || '');
            const nuevoEstado = String(data.usuario?.estado || formData.get('estado') || 'Activo');
            const esAdmin     = (nuevoRol === 'admin' || nuevoRol === 'administrador');

            const filas = document.querySelectorAll('.users-table tbody tr');
            filas.forEach(fila => {
                const btnEdit = fila.querySelector('.btn-editar-usuario');
                if (btnEdit && btnEdit.getAttribute('onclick')?.includes(idUsr)) {
                    // Actualizar nombre e iniciales
                    const avatarTag = fila.querySelector('.avatar-circle-sm, .user-avatar-tag');
                    const nameTitle = fila.querySelector('.cell-nombre, .user-name-title');
                    const emailText = fila.querySelector('.cell-correo, .user-email-text');
                    const roleBadge = fila.querySelector('.role-pill, .role-badge');
                    const statusTag = fila.querySelector('.status-pill-active span:last-child, .status-badge-ok');

                    if (avatarTag) avatarTag.textContent = obtenerInicialesJs(nuevoNombre);
                    if (nameTitle) nameTitle.textContent = nuevoNombre;
                    if (emailText) emailText.textContent = nuevoCorreo;

                    if (roleBadge) {
                        if (esAdmin) {
                            roleBadge.className = 'role-pill pill-role-admin';
                            roleBadge.innerHTML = '<span class="role-dot-admin" aria-hidden="true"></span><span>Administrador</span>';
                        } else {
                            roleBadge.className = 'role-pill pill-role-estandar';
                            roleBadge.innerHTML = '<span class="role-dot-estandar" aria-hidden="true"></span><span>Estándar</span>';
                        }
                    }

                    if (statusTag) {
                        statusTag.textContent = nuevoEstado;
                    }

                    fila.setAttribute('data-filtro', `${nuevoNombre} ${nuevoCorreo} ${esAdmin ? 'admin administrador' : 'estandar'}`.toLowerCase());

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
