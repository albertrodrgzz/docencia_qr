<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al Sistema de Certificación Institucional ISPEB">
    <title>Iniciar Sesión — ISPEB Certificaciones</title>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Reset y base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --apple-blue:     #007AFF;
            --apple-blue-dk:  #0062CC;
            --apple-red:      #FF3B30;
            --apple-gray:     #8e8e93;
            --apple-bg:       #f5f5f7;
            --apple-border:   #d2d2d7;
            --apple-text:     #1d1d1f;
            --apple-subtext:  #6e6e73;
            --card-shadow:    0 10px 30px rgba(0, 0, 0, 0.05);
            --font:           -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'Segoe UI', Helvetica, Arial, sans-serif;
        }

        html, body {
            height: 100%;
            font-family: var(--font);
            background-color: var(--apple-bg);
            color: var(--apple-text);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Layout centrado ── */
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            gap: 28px;
        }

        /* ── Encabezado institucional ── */
        .brand-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            text-align: center;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: var(--apple-blue);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.30);
        }

        .brand-icon svg {
            width: 34px;
            height: 34px;
            fill: #fff;
        }

        .brand-name {
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            color: var(--apple-text);
        }

        .brand-sub {
            font-size: 0.80rem;
            color: var(--apple-subtext);
            font-weight: 400;
        }

        /* ── Tarjeta principal ── */
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            padding: 40px 36px 36px;
        }

        .login-card__title {
            font-size: 1.50rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--apple-text);
            margin-bottom: 4px;
        }

        .login-card__subtitle {
            font-size: 0.875rem;
            color: var(--apple-subtext);
            margin-bottom: 28px;
            font-weight: 400;
        }

        /* ── Grupos de campo ── */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--apple-subtext);
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--apple-gray);
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .input-icon svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            font-family: var(--font);
            font-size: 0.95rem;
            color: var(--apple-text);
            background: #f5f5f7;
            border: 1.5px solid var(--apple-border);
            border-radius: 12px;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            -webkit-appearance: none;
        }

        .form-input::placeholder { color: #aeaeb2; }

        .form-input:focus {
            border-color: var(--apple-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.12);
        }

        /* ── Toggle contraseña ── */
        #btn-toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--apple-gray);
            display: flex;
            padding: 4px;
            border-radius: 6px;
            transition: color 0.15s;
        }

        #btn-toggle-pass:hover { color: var(--apple-blue); }
        #btn-toggle-pass svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 1.8; }

        /* ── Botón primario ── */
        .btn-primary {
            width: 100%;
            padding: 14px;
            margin-top: 24px;
            font-family: var(--font);
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: var(--apple-blue);
            border: none;
            border-radius: 14px;
            cursor: pointer;
            letter-spacing: -0.01em;
            transition: background 0.18s ease, transform 0.12s ease, box-shadow 0.18s ease;
            box-shadow: 0 4px 14px rgba(0, 122, 255, 0.30);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--apple-blue-dk);
            box-shadow: 0 6px 18px rgba(0, 98, 204, 0.35);
        }

        .btn-primary:active:not(:disabled) {
            transform: scale(0.98);
        }

        .btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .btn-primary .btn-spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            vertical-align: middle;
            margin-right: 6px;
        }

        .btn-primary.loading .btn-spinner { display: inline-block; }
        .btn-primary.loading .btn-text    { opacity: 0.8; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Footer de la tarjeta ── */
        .card-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.75rem;
            color: var(--apple-subtext);
        }

        /* ── Footer de página ── */
        .page-footer {
            font-size: 0.72rem;
            color: var(--apple-subtext);
            text-align: center;
        }

        /* ── Animación de entrada ── */
        .login-card {
            animation: slideUp 0.38s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .login-card { padding: 32px 24px 28px; border-radius: 16px; }
            .login-card__title { font-size: 1.3rem; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- ── Encabezado institucional ── -->
    <div class="brand-header">
        <div class="brand-icon" aria-hidden="true">
            <!-- Ícono certificado/sello -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L3 6v6c0 5.25 3.75 10.15 9 11.25C17.25 22.15 21 17.25 21 12V6L12 2zm-1.5 13.5L7 12l1.5-1.5 2 2 5-5 1.5 1.5-6.5 6.5z"/>
            </svg>
        </div>
        <div>
            <p class="brand-name">ISPEB</p>
            <p class="brand-sub">Sistema Institucional de Certificaciones</p>
        </div>
    </div>

    <!-- ── Tarjeta de login ── -->
    <div class="login-card" role="main">
        <h1 class="login-card__title">Bienvenido</h1>
        <p class="login-card__subtitle">Ingresa tus credenciales institucionales para continuar.</p>

        <form id="form-login" novalidate>

            <!-- Campo: Correo -->
            <div class="form-group">
                <label class="form-label" for="correo">Correo electrónico</label>
                <div class="input-wrapper">
                    <span class="input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 7l10 7L22 7"/></svg>
                    </span>
                    <input
                        type="email"
                        id="correo"
                        name="correo"
                        class="form-input"
                        placeholder="usuario@ispeb.gob.ve"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <!-- Campo: Contraseña -->
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <div class="input-wrapper">
                    <span class="input-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" id="btn-toggle-pass" aria-label="Mostrar u ocultar contraseña">
                        <!-- Ojo abierto -->
                        <svg id="icon-eye-open" viewBox="0 0 24 24"><path d="M1 12S5 5 12 5s11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        <!-- Ojo cerrado (oculto por defecto) -->
                        <svg id="icon-eye-closed" viewBox="0 0 24 24" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.4 18.4 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07A3 3 0 019.12 9.1"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>

            <!-- Botón submit -->
            <button type="submit" id="btn-ingresar" class="btn-primary">
                <span class="btn-spinner" aria-hidden="true"></span>
                <span class="btn-text">Ingresar al sistema</span>
            </button>

        </form>

        <div class="card-footer">
            <p>¿Problemas para ingresar? Contacta al administrador del sistema.</p>
        </div>
    </div>

    <!-- ── Footer de página ── -->
    <footer class="page-footer">
        <p>© <?= date('Y') ?> ISPEB · Todos los derechos reservados</p>
    </footer>

</div>

<!-- ── Script de login con Fetch API ── -->
<script>
(function () {
    'use strict';

    const BASE_URL    = '/docencia_qr';
    const formLogin   = document.getElementById('form-login');
    const btnIngresar = document.getElementById('btn-ingresar');
    const inputPass   = document.getElementById('password');
    const btnToggle   = document.getElementById('btn-toggle-pass');
    const iconOpen    = document.getElementById('icon-eye-open');
    const iconClosed  = document.getElementById('icon-eye-closed');

    // ── Toggle visibilidad de contraseña ────────────────────────────────────
    btnToggle.addEventListener('click', () => {
        const isPassword = inputPass.type === 'password';
        inputPass.type   = isPassword ? 'text' : 'password';
        iconOpen.style.display   = isPassword ? 'none'  : 'block';
        iconClosed.style.display = isPassword ? 'block' : 'none';
    });

    // ── Interceptar submit del formulario ───────────────────────────────────
    formLogin.addEventListener('submit', async function (e) {
        e.preventDefault();

        const correo   = document.getElementById('correo').value.trim();
        const password = inputPass.value.trim();

        // Validación mínima en cliente
        if (!correo || !password) {
            Swal.fire({
                icon:             'warning',
                title:            'Campos requeridos',
                text:             'Ingresa tu correo y contraseña para continuar.',
                confirmButtonColor: '#007AFF',
                confirmButtonText: 'Entendido',
            });
            return;
        }

        // Estado de carga
        setBtnLoading(true);

        // Alerta de validación en progreso
        Swal.fire({
            title:             'Validando…',
            text:              'Verificando tus credenciales institucionales.',
            allowOutsideClick: false,
            allowEscapeKey:    false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const formData = new FormData();
            formData.append('correo',   correo);
            formData.append('password', password);

            const response = await fetch(BASE_URL + '/login', {
                method:      'POST',
                body:        formData,
                credentials: 'same-origin',
                headers:     { 'X-Requested-With': 'XMLHttpRequest' },
            });

            let data;
            try {
                data = await response.json();
            } catch {
                throw new Error('La respuesta del servidor no es válida (no es JSON).');
            }

            if (data.exito) {
                // ── Éxito: redirigir al dashboard ────────────────────────
                await Swal.fire({
                    icon:             'success',
                    title:            '¡Bienvenido!',
                    text:             `Acceso concedido. Redirigiendo al panel...`,
                    confirmButtonColor: '#007AFF',
                    timer:            1800,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
                window.location.href = data.redirigir || (BASE_URL + '/dashboard');

            } else {
                // ── Fallo: mostrar mensaje del servidor ───────────────────
                Swal.fire({
                    icon:             'error',
                    title:            'Acceso denegado',
                    text:             data.mensaje || 'Credenciales incorrectas.',
                    confirmButtonColor: '#007AFF',
                    confirmButtonText: 'Reintentar',
                    customClass:      { popup: 'swal-ispeb' },
                });
                setBtnLoading(false);
            }

        } catch (error) {
            console.error('[ISPEB Login Error]', error);
            Swal.fire({
                icon:             'error',
                title:            'Error de conexión',
                text:             'No se pudo conectar con el servidor. Inténtalo nuevamente.',
                confirmButtonColor: '#007AFF',
                confirmButtonText:  'Cerrar',
            });
            setBtnLoading(false);
        }
    });

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Controla el estado visual de carga del botón de submit.
     * @param {boolean} loading
     */
    function setBtnLoading(loading) {
        btnIngresar.disabled = loading;
        btnIngresar.classList.toggle('loading', loading);
    }

}());
</script>

</body>
</html>
