<?php
/**
 * Vista: Mi Perfil — ISPEB Sistema Institucional
 * Ecosistema macOS / iOS con CSS Puro y Plantillas Maestras
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
    /* ── Estilos Específicos: Tarjeta de Perfil Estilo Apple ── */
    .profile-container-card {
        max-width: 640px;
        margin: 0 auto;
        padding: 40px 36px;
        text-align: center;
    }

    /* Avatar grande y circular centrado */
    .profile-avatar-large {
        width: 108px;
        height: 108px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
        color: #ffffff;
        font-size: 2.4rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        box-shadow: 0 12px 28px rgba(0, 113, 227, 0.28);
        border: 4px solid #ffffff;
        outline: 1px solid rgba(0, 0, 0, 0.06);
    }

    /* Nombre del usuario en grande */
    .profile-user-name {
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        color: #1d1d1f;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }

    /* Badge estilizado para el nivel de acceso / rol */
    .profile-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        letter-spacing: -0.01em;
        margin-bottom: 32px;
    }

    /* Azul pastel para Admin */
    .profile-role-badge.role-admin {
        background: rgba(0, 113, 227, 0.10);
        color: #0071e3;
        border: 1px solid rgba(0, 113, 227, 0.20);
    }

    /* Gris sutil para Estándar */
    .profile-role-badge.role-estandar {
        background: rgba(0, 0, 0, 0.05);
        color: #6e6e73;
        border: 1px solid rgba(0, 0, 0, 0.08);
    }

    .badge-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    /* Separador sutil */
    .profile-divider {
        height: 1px;
        background: #e5e5ea;
        margin: 0 0 28px 0;
        border: none;
    }

    /* Formulario de Información (Solo Lectura) */
    .profile-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        text-align: left;
    }

    .profile-field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .profile-field-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #86868b;
        display: flex;
        align-items: center;
        gap: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .profile-field-label i {
        font-size: 0.80rem;
        color: #86868b;
    }

    .profile-field-input-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .profile-input-readonly {
        width: 100%;
        height: 44px;
        padding: 0 16px;
        border-radius: 12px;
        border: 1px solid #e5e5ea;
        background: #f5f5f7;
        font-family: inherit;
        font-size: 0.92rem;
        color: #1d1d1f;
        font-weight: 500;
        box-sizing: border-box;
        outline: none;
        cursor: default;
    }

    .profile-input-readonly.mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.88rem;
    }

    /* Barra de estado inferior */
    .profile-footer-status {
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid #e5e5ea;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .status-text-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.82rem;
        color: #34c759;
        font-weight: 600;
    }

    .status-text-item i {
        font-size: 0.88rem;
    }

    .btn-logout-card {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 36px;
        padding: 0 16px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.84rem;
        font-weight: 600;
        color: #c93b2b;
        background: rgba(201, 59, 43, 0.08);
        border: 1px solid rgba(201, 59, 43, 0.15);
        transition: all 0.16s ease;
    }

    .btn-logout-card:hover {
        background: rgba(201, 59, 43, 0.16);
        color: #b32617;
        transform: translateY(-1px);
    }
</style>

<!-- ── Contenedor de Contenido (.content-wrapper) ── -->
<div class="content-wrapper">

    <!-- ── Tarjeta de Presentación Estilo Apple (.content-card) ── -->
    <div class="content-card profile-container-card">

        <!-- 1. Avatar Grande y Circular Centrado -->
        <div class="profile-avatar-large" aria-hidden="true">
            <?= $badgeIniciales ?>
        </div>

        <!-- 2. Nombre del Usuario en Grande -->
        <h2 class="profile-user-name"><?= $nombreUsuario ?></h2>

        <!-- 3. Nivel de Acceso / Rol con Badge Estilizado -->
        <span class="profile-role-badge <?= $esAdmin ? 'role-admin' : 'role-estandar' ?>">
            <span class="badge-dot"></span>
            Rol: <?= $rolUsuario ?>
        </span>

        <hr class="profile-divider">

        <!-- 4. Formulario de Información (Solo Lectura) -->
        <div class="profile-form-grid">
            
            <div class="profile-field-group">
                <label class="profile-field-label">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <span>Correo Electrónico Institucional</span>
                </label>
                <div class="profile-field-input-box">
                    <input 
                        type="email" 
                        class="profile-input-readonly mono" 
                        value="<?= $correoUsuario ?>" 
                        readonly 
                        aria-label="Correo institucional"
                    >
                </div>
            </div>

            <div class="profile-field-group">
                <label class="profile-field-label">
                    <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
                    <span>Departamento / Unidad de Adscripción</span>
                </label>
                <div class="profile-field-input-box">
                    <input 
                        type="text" 
                        class="profile-input-readonly" 
                        value="Dirección de Docencia y Gestión Curricular" 
                        readonly 
                        aria-label="Departamento"
                    >
                </div>
            </div>

            <div class="profile-field-group">
                <label class="profile-field-label">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    <span>Privilegios de Seguridad (RBAC)</span>
                </label>
                <div class="profile-field-input-box">
                    <input 
                        type="text" 
                        class="profile-input-readonly" 
                        value="<?= $esAdmin ? 'Acceso Total al Sistema (Administrador)' : 'Acceso Estándar (Lectura y Emisión QR)' ?>" 
                        readonly 
                        aria-label="Nivel de privilegios"
                    >
                </div>
            </div>

        </div>

        <!-- 5. Barra de Estado y Cierre de Sesión -->
        <div class="profile-footer-status">
            <span class="status-text-item">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>Cuenta institucional activa y verificada</span>
            </span>

            <a href="javascript:void(0)" onclick="confirmarSalida(event)" class="btn-logout-card" title="Finalizar sesión">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>

    </div>

</div> <!-- /.content-wrapper -->

<?php require_once __DIR__ . '/layout/footer.php'; ?>
