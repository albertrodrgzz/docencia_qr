<?php
/**
 * Vista de Inicio de Sesión — ISPEB Sistema Institucional de Certificaciones
 * Diseño Hiper-minimalista inspirado en macOS / iOS (Glassmorphism, SF Pro, Apple HIG)
 */

$baseUrl = (isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) 
    ? '/docencia_qr' 
    : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#f5f5f7">
    <meta name="description" content="Acceso al Sistema de Certificación Institucional ISPEB">
    <title>Iniciar Sesión — ISPEB</title>

    <!-- SweetAlert2 para notificaciones fluidas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ── Variables de Diseño Apple macOS / iOS ── */
        :root {
            --apple-blue: #0071e3;
            --apple-blue-hover: #0077ed;
            --apple-blue-active: #005bb5;
            --apple-focus-ring: rgba(0, 113, 227, 0.22);
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-tertiary: #a1a1a6;
            --glass-bg: rgba(255, 255, 255, 0.62);
            --glass-border: rgba(255, 255, 255, 0.75);
            --input-bg: rgba(0, 0, 0, 0.035);
            --input-border: rgba(0, 0, 0, 0.07);
            --input-bg-focus: rgba(255, 255, 255, 0.95);
            --card-radius: 24px;
            --control-radius: 13px;
            --font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Helvetica, Arial, sans-serif;
            --shadow-subtle: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
            --shadow-float: 0 20px 48px -10px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(255, 255, 255, 0.6) inset;
        }

        /* ── Reset y Base ── */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            min-height: 100vh;
            font-family: var(--font-family);
            background-color: #f5f5f7;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── Fondo abstracto dinámico estilo Apple Sonoma / Sequoia ── */
        .ambient-canvas {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
            background: 
                radial-gradient(circle at 18% 22%, rgba(206, 226, 255, 0.65) 0%, transparent 45%),
                radial-gradient(circle at 82% 18%, rgba(228, 219, 255, 0.55) 0%, transparent 48%),
                radial-gradient(circle at 50% 88%, rgba(210, 238, 255, 0.6) 0%, transparent 52%),
                radial-gradient(circle at 85% 85%, rgba(243, 230, 255, 0.5) 0%, transparent 45%),
                #f5f5f7;
        }

        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            animation: orbFloat 18s ease-in-out infinite alternate;
        }

        .orb-1 {
            width: 380px;
            height: 380px;
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
            top: 5%;
            left: 15%;
        }

        .orb-2 {
            width: 440px;
            height: 440px;
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            bottom: 8%;
            right: 12%;
            animation-duration: 22s;
            animation-delay: -5s;
        }

        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(25px, -30px) scale(1.06); }
            100% { transform: translate(-20px, 20px) scale(0.96); }
        }

        /* ── Contenedor Principal Centrado ── */
        .main-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 410px;
            padding: 24px 20px;
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* ── Tarjeta Glassmorphism Apple ── */
        .login-card {
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(30px) saturate(190%);
            -webkit-backdrop-filter: blur(30px) saturate(190%);
            border: 1px solid var(--glass-border);
            border-radius: var(--card-radius);
            box-shadow: var(--shadow-float);
            padding: 38px 34px 34px;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
            animation: cardAppear 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Efecto Shake estilo macOS al fallar contraseña */
        .shake-animation {
            animation: macShake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes macShake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        /* ── Cabecera de la Tarjeta e Isotipo ── */
        .card-header {
            text-align: center;
            margin-bottom: 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .app-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0077ed 0%, #0051c6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 6px 16px rgba(0, 119, 237, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.4);
            margin-bottom: 16px;
            transition: transform 0.2s ease;
        }

        .app-icon:hover {
            transform: scale(1.03);
        }

        .app-icon svg {
            width: 30px;
            height: 30px;
            fill: currentColor;
        }

        .brand-title {
            font-size: 1.45rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .brand-subtitle {
            margin-top: 5px;
            font-size: 0.86rem;
            color: var(--text-secondary);
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        /* ── Campos del Formulario ── */
        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 7px;
            margin-left: 2px;
            letter-spacing: -0.01em;
        }

        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-leading-icon {
            position: absolute;
            left: 14px;
            color: var(--text-tertiary);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .input-leading-icon svg {
            width: 17px;
            height: 17px;
            stroke-width: 2;
        }

        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 42px;
            font-family: var(--font-family);
            font-size: 0.92rem;
            color: var(--text-primary);
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: var(--control-radius);
            outline: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            -webkit-appearance: none;
        }

        .form-input::placeholder {
            color: var(--text-tertiary);
            font-weight: 400;
        }

        .form-input:hover {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.12);
        }

        /* Focus Apple: anillo azul brillante y fondo blanco puro */
        .form-input:focus {
            background: var(--input-bg-focus);
            border-color: var(--apple-blue);
            box-shadow: 0 0 0 3.5px var(--apple-focus-ring);
        }

        .form-input:focus ~ .input-leading-icon {
            color: var(--apple-blue);
        }

        /* ── Botón Mostrar/Ocultar Contraseña ── */
        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--text-tertiary);
            padding: 6px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, background 0.15s ease;
        }

        .password-toggle-btn:hover {
            color: var(--text-primary);
            background: rgba(0, 0, 0, 0.04);
        }

        .password-toggle-btn svg {
            width: 18px;
            height: 18px;
            stroke-width: 1.8;
            stroke: currentColor;
            fill: none;
        }

        /* ── Botón Submit Estilo iOS ── */
        .btn-submit {
            width: 100%;
            height: 46px;
            margin-top: 10px;
            background: var(--apple-blue);
            color: #ffffff;
            border: none;
            border-radius: var(--control-radius);
            font-family: var(--font-family);
            font-size: 0.94rem;
            font-weight: 600;
            letter-spacing: -0.015em;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 113, 227, 0.22);
            transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover:not(:disabled) {
            background: var(--apple-blue-hover);
            box-shadow: 0 6px 16px rgba(0, 113, 227, 0.3);
            transform: translateY(-0.5px);
        }

        .btn-submit:active:not(:disabled) {
            background: var(--apple-blue-active);
            transform: scale(0.985);
            box-shadow: 0 2px 6px rgba(0, 113, 227, 0.2);
        }

        .btn-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Spinner estilo iOS */
        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.65s linear infinite;
        }

        .btn-submit.loading .spinner {
            display: block;
        }

        .btn-submit.loading .btn-text {
            opacity: 0.85;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ── Pie de Tarjeta ── */
        .card-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 0.76rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .card-footer a {
            color: var(--apple-blue);
            text-decoration: none;
            transition: underline 0.15s ease;
        }

        .card-footer a:hover {
            text-decoration: underline;
        }

        /* ── Pie de Página ── */
        .footer-note {
            margin-top: 24px;
            font-size: 0.72rem;
            color: var(--text-tertiary);
            text-align: center;
            letter-spacing: -0.01em;
            animation: cardAppear 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        /* ── Estilos personalizados para SweetAlert2 macOS ── */
        .swal2-popup.apple-modal {
            border-radius: 20px !important;
            padding: 24px !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(25px) !important;
            -webkit-backdrop-filter: blur(25px) !important;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.12) !important;
            font-family: var(--font-family) !important;
        }

        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 600 !important;
            color: var(--text-primary) !important;
        }

        .swal2-html-container {
            font-size: 0.88rem !important;
            color: var(--text-secondary) !important;
            margin-top: 6px !important;
        }

        .swal2-confirm.apple-btn {
            background-color: var(--apple-blue) !important;
            border-radius: 10px !important;
            padding: 10px 24px !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            box-shadow: none !important;
        }

        /* ── Modo Responsivo ── */
        @media (max-width: 440px) {
            .login-card {
                padding: 30px 22px 26px;
                border-radius: 20px;
            }

            .brand-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>

    <!-- Lienzo con orbes de fondo -->
    <div class="ambient-canvas" aria-hidden="true">
        <div class="ambient-orb orb-1"></div>
        <div class="ambient-orb orb-2"></div>
    </div>

    <!-- Envoltorio principal -->
    <main class="main-wrapper">
        <div class="login-card" id="loginCard">
            
            <!-- Encabezado Institucional -->
            <div class="card-header">
                <div class="app-icon" aria-hidden="true">
                    <!-- Ícono de Seguridad y Certificado -->
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-1 16l-4-4 1.41-1.41L11 14.17l6.59-6.59L19 9l-8 8z"/>
                    </svg>
                </div>
                <h1 class="brand-title">ISPEB</h1>
                <p class="brand-subtitle">Sistema Institucional de Certificaciones</p>
            </div>

            <!-- Formulario de Autenticación -->
            <form id="form-login" action="<?= htmlspecialchars($baseUrl) ?>/login" method="POST" novalidate autocomplete="on">
                
                <!-- Correo Institucional -->
                <div class="form-group">
                    <label class="form-label" for="correo">Correo electrónico</label>
                    <div class="input-container">
                        <span class="input-leading-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="2" y="4" width="20" height="16" rx="3"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="correo"
                            name="correo"
                            class="form-input"
                            placeholder="usuario@ispeb.gob.ve"
                            autocomplete="email"
                            required
                            spellcheck="false"
                            autofocus
                        >
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña</label>
                    <div class="input-container">
                        <span class="input-leading-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" id="btnTogglePass" class="password-toggle-btn" aria-label="Mostrar u ocultar contraseña" tabindex="-1">
                            <svg id="eyeOpenIcon" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eyeClosedIcon" viewBox="0 0 24 24" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07A3 3 0 1 1 12 15a3 3 0 0 1-2.88-2.88"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Botón de Envío -->
                <button type="submit" id="btnSubmit" class="btn-submit">
                    <span class="spinner" aria-hidden="true"></span>
                    <span class="btn-text">Iniciar sesión</span>
                </button>

            </form>

            <div class="card-footer">
                <p>¿Olvidaste tu contraseña o requieres soporte?<br>Contacta a la Oficina de Tecnología y Sistemas.</p>
            </div>
        </div>

        <footer class="footer-note">
            <p>© <?= date('Y') ?> Instituto Autónomo de Policía del Estado Bolívar · ISPEB</p>
        </footer>
    </main>

    <!-- ── Lógica Frontend (Fetch asíncrono con fallback y feedback háptico) ── -->
    <script>
    (function () {
        'use strict';

        const BASE_URL      = '<?= $baseUrl ?>';
        const formLogin     = document.getElementById('form-login');
        const loginCard     = document.getElementById('loginCard');
        const btnSubmit     = document.getElementById('btnSubmit');
        const inputCorreo   = document.getElementById('correo');
        const inputPassword = document.getElementById('password');
        const btnTogglePass = document.getElementById('btnTogglePass');
        const eyeOpenIcon   = document.getElementById('eyeOpenIcon');
        const eyeClosedIcon = document.getElementById('eyeClosedIcon');

        // Toggle visibilidad de contraseña
        btnTogglePass.addEventListener('click', function () {
            const isPassword = inputPassword.type === 'password';
            inputPassword.type = isPassword ? 'text' : 'password';
            eyeOpenIcon.style.display = isPassword ? 'none' : 'block';
            eyeClosedIcon.style.display = isPassword ? 'block' : 'none';
            inputPassword.focus();
        });

        // Animación Shake estilo macOS al detectar error
        function triggerShake() {
            loginCard.classList.remove('shake-animation');
            // Forzar reflow para reiniciar la animación
            void loginCard.offsetWidth;
            loginCard.classList.add('shake-animation');
        }

        // Estado de carga del botón
        function setLoading(isLoading) {
            btnSubmit.disabled = isLoading;
            btnSubmit.classList.toggle('loading', isLoading);
        }

        // Interceptación de envío con Fetch API
        formLogin.addEventListener('submit', async function (e) {
            e.preventDefault();

            const correo = inputCorreo.value.trim();
            const password = inputPassword.value.trim();

            if (!correo || !password) {
                triggerShake();
                Swal.fire({
                    title: 'Campos requeridos',
                    text: 'Por favor ingresa tu correo institucional y contraseña.',
                    icon: 'warning',
                    confirmButtonText: 'Continuar',
                    customClass: {
                        popup: 'apple-modal',
                        confirmButton: 'apple-btn'
                    },
                    buttonsStyling: false
                });
                return;
            }

            setLoading(true);

            try {
                const formData = new FormData();
                formData.append('correo', correo);
                formData.append('password', password);

                const response = await fetch(formLogin.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                let data;
                try {
                    data = await response.json();
                } catch (parseError) {
                    throw new Error('Respuesta inválida del servidor.');
                }

                if (data.exito) {
                    // Acceso exitoso
                    Swal.fire({
                        title: 'Acceso Concedido',
                        text: 'Iniciando sesión...',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1200,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'apple-modal'
                        }
                    }).then(() => {
                        window.location.href = data.redirigir || (BASE_URL + '/dashboard');
                    });
                } else {
                    // Acceso denegado
                    triggerShake();
                    Swal.fire({
                        title: 'Credenciales Incorrectas',
                        text: data.mensaje || 'El correo o la contraseña no coinciden.',
                        icon: 'error',
                        confirmButtonText: 'Intentar de nuevo',
                        customClass: {
                            popup: 'apple-modal',
                            confirmButton: 'apple-btn'
                        },
                        buttonsStyling: false
                    });
                    setLoading(false);
                    inputPassword.value = '';
                    inputPassword.focus();
                }

            } catch (err) {
                console.error('[ISPEB Auth Error]', err);
                triggerShake();
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'No fue posible comunicar con el servidor institucional.',
                    icon: 'error',
                    confirmButtonText: 'Entendido',
                    customClass: {
                        popup: 'apple-modal',
                        confirmButton: 'apple-btn'
                    },
                    buttonsStyling: false
                });
                setLoading(false);
            }
        });
    })();
    </script>
</body>
</html>
