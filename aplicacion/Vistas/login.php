<?php
$page_title = 'Inicio de Sesión';

/**
 * Vista de Inicio de Sesión — Kredentia
 * Diseño: Ultra-Premium Split Card Layout con Columna Izquierda Cristalina y Fondo Dot-Grid
 */

$baseUrl = defined('BASE_URL') 
    ? BASE_URL 
    : ((isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) ? '/docencia_qr' : '');

$assetPrefix = !empty($baseUrl) ? rtrim($baseUrl, '/') . '/' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#f8fafc">
    <meta name="description" content="Kredentia — Plataforma de Autenticación Institucional">
    <title><?= !empty($page_title) ? htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') . ' | Kredentia' : 'Inicio de Sesión | Kredentia' ?></title>

    <!-- Favicon Kredentia -->
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetPrefix) ?>assets/img/favicon.png">

    <!-- Tipografía Inter & Apple Fallbacks -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- SweetAlert2 para notificaciones fluidas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Hoja de estilos externa -->
    <link rel="stylesheet" href="<?= htmlspecialchars($assetPrefix) ?>assets/css/login.css">

    <style>
        /* ── Variables de Diseño ── */
        :root {
            --font-apple: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", "Helvetica Neue", sans-serif;
            --color-indigo-dark: #1e1b4b;
            --color-accent-indigo: #4f46e5;
            --color-accent-focus: #6366f1;
            --color-accent-cyan: #06b6d4;
            --color-text-title: #0f172a;
            --color-text-body: #64748b;
            --color-text-label: #475569;
            --color-input-bg: #f8fafc;
            --color-input-border: #e2e8f0;
        }

        /* ── Reset y Normalización ── */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        /* ── 1. Fondo de Pantalla Principal (El Body con Textura Dot-Grid + Mesh Gradient) ── */
        body {
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-apple);
            color: var(--color-text-title);
            background-color: #f8fafc;
            background-image: 
                radial-gradient(rgba(79, 70, 229, 0.08) 2px, transparent 2px), 
                radial-gradient(rgba(6, 182, 212, 0.05) 1px, transparent 1px);
            background-size: 32px 32px, 16px 16px;
            background-position: 0 0, 8px 8px;
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            position: relative;
        }

        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        body::before {
            width: 480px;
            height: 480px;
            background: rgba(6, 182, 212, 0.15);
            top: -80px;
            right: -60px;
        }

        body::after {
            width: 520px;
            height: 520px;
            background: rgba(79, 70, 229, 0.10);
            bottom: -100px;
            left: -80px;
        }

        .login-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        /* ── 2. Tarjeta Dividida (.login-card-horizontal) ── */
        .login-card-horizontal {
            display: flex;
            flex-direction: row;
            max-width: 960px;
            width: 100%;
            min-height: 560px;
            border-radius: 32px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(50px);
            -webkit-backdrop-filter: blur(50px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 60px 120px rgba(15, 23, 42, 0.15), 0 20px 40px rgba(15, 23, 42, 0.08);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
            animation: cardEntrance 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes cardEntrance {
            0% {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ── 3. Columna Izquierda - Branding Cristalino (.brand-section) ── */
        .brand-section {
            flex: 1.1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 48px;
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e0e7ff 100%);
            border-right: 1px solid rgba(226, 232, 240, 0.6);
            overflow: hidden;
            box-sizing: border-box;
        }

        .brand-glow {
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle at center, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }

        .brand-logo-img {
            width: 85%;
            max-width: 280px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 10px 20px rgba(79, 70, 229, 0.1));
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
            position: relative;
            z-index: 2;
            user-select: none;
            -webkit-user-drag: none;
        }

        .brand-logo-img:hover {
            transform: translateY(-3px) scale(1.02);
            filter: drop-shadow(0 14px 28px rgba(79, 70, 229, 0.15));
        }

        /* ── 4. Columna Derecha - Formulario Estilizado (.form-section) ── */
        .form-section {
            flex: 0.9;
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }

        .welcome-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--color-text-title);
            margin: 0 0 8px 0;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .welcome-subtitle {
            color: var(--color-text-body);
            font-size: 15px;
            margin: 0 0 40px 0;
            line-height: 1.4;
        }

        /* Formulario */
        .login-form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text-label);
            margin-bottom: 6px;
            letter-spacing: -0.01em;
            display: block;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .form-input {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            border: 1.5px solid var(--color-input-border);
            background: var(--color-input-bg);
            padding: 14px 18px;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.25s ease;
            color: var(--color-text-title);
            box-sizing: border-box;
            outline: none;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Estado Focus */
        .form-input:focus {
            border-color: var(--color-accent-focus);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        /* Toggle de contraseña */
        .input-with-toggle .form-input {
            padding-right: 48px;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            padding: 6px;
            cursor: pointer;
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: color 0.18s ease, background-color 0.18s ease;
        }

        .password-toggle:hover {
            color: var(--color-text-title);
            background-color: rgba(0, 0, 0, 0.04);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }

        /* Botón de Enviar */
        .btn-login-horizontal {
            width: 100%;
            height: 52px;
            background: var(--color-indigo-dark);
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            color: #ffffff;
            cursor: pointer;
            border: none;
            padding: 14px;
            margin-top: 16px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(30, 27, 75, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }

        .btn-login-horizontal:hover {
            background: var(--color-accent-indigo);
            transform: translateY(-1.5px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .btn-login-horizontal:active {
            transform: translateY(0.5px) scale(0.99);
        }

        .btn-login-horizontal:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Spinner indicador de carga */
        .btn-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.35);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: btnSpin 0.65s linear infinite;
        }

        .btn-login-horizontal.is-loading .btn-spinner {
            display: block;
        }

        @keyframes btnSpin {
            to { transform: rotate(360deg); }
        }

        /* ── Efecto Shake estilo macOS al fallar ── */
        .shake {
            animation: macShake 0.45s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        @keyframes macShake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-5px, 0, 0); }
            40%, 60% { transform: translate3d(5px, 0, 0); }
        }

        /* ── SweetAlert2 estilo Apple ── */
        .swal2-popup.apple-alert {
            border-radius: 20px !important;
            padding: 24px !important;
            background: rgba(255, 255, 255, 0.96) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            box-shadow: 0 20px 48px rgba(0, 0, 0, 0.12) !important;
            font-family: var(--font-apple) !important;
        }

        .swal2-title {
            font-size: 1.15rem !important;
            font-weight: 600 !important;
            color: var(--color-text-title) !important;
        }

        .swal2-html-container {
            font-size: 0.88rem !important;
            color: var(--color-text-body) !important;
            margin-top: 8px !important;
        }

        .swal2-confirm.apple-confirm-btn {
            background: linear-gradient(135deg, #1e1b4b, #312e81) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            padding: 12px 28px !important;
            font-size: 0.92rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.01em !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 4px 14px rgba(30, 27, 75, 0.35) !important;
            cursor: pointer !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            outline: none !important;
        }

        .swal2-confirm.apple-confirm-btn:hover {
            background: linear-gradient(135deg, #2e1065, #3730a3) !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(49, 46, 129, 0.45) !important;
        }

        .swal2-confirm.apple-confirm-btn:active {
            transform: translateY(0) !important;
        }

        /* ── Responsividad (Móviles / Tablets < 768px) ── */
        @media (max-width: 768px) {
            body {
                padding: 16px;
                min-height: 100vh;
                height: auto;
            }

            .login-card-horizontal {
                flex-direction: column;
                max-width: 440px;
                min-height: auto;
                border-radius: 24px;
            }

            .brand-section {
                padding: 32px 24px;
                border-right: none;
                border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            }

            .brand-logo-img {
                max-width: 180px;
            }

            .form-section {
                padding: 32px 24px;
            }

            .welcome-title {
                font-size: 24px;
            }

            .welcome-subtitle {
                margin-bottom: 28px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

    <main class="login-wrapper">
        <!-- 2. La Tarjeta Dividida (.login-card-horizontal) -->
        <div class="login-card-horizontal" id="loginCard">
            
            <!-- 3. Columna Izquierda - Branding Cristalino (.brand-section) -->
            <section class="brand-section">
                <div class="brand-glow" aria-hidden="true"></div>
                <img src="<?= htmlspecialchars($assetPrefix) ?>assets/img/login-logo.png" alt="Kredentia" class="brand-logo-img">
            </section>

            <!-- 4. Columna Derecha - El Formulario Estilizado (.form-section) -->
            <section class="form-section">
                
                <!-- Header -->
                <h2 class="welcome-title">Bienvenido</h2>
                <p class="welcome-subtitle">Ingresa tus credenciales para continuar.</p>

                <!-- Formulario -->
                <form id="formLogin" class="login-form" action="<?= htmlspecialchars($baseUrl) ?>/login" method="POST" novalidate autocomplete="on">
                    
                    <!-- Campo Correo / Usuario -->
                    <div class="form-group">
                        <label class="form-label" for="correo">Correo institucional</label>
                        <div class="input-wrapper">
                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                class="form-input"
                                placeholder="usuario@kredentia.com"
                                autocomplete="username email"
                                required
                                spellcheck="false"
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="form-group">
                        <label class="form-label" for="password">Contraseña</label>
                        <div class="input-wrapper input-with-toggle">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="••••••••••••"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" id="btnTogglePassword" class="password-toggle" aria-label="Mostrar u ocultar contraseña" tabindex="-1">
                                <svg id="iconEyeOpen" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg id="iconEyeClosed" viewBox="0 0 24 24" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07A3 3 0 1 1 12 15a3 3 0 0 1-2.88-2.88"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Botón de Enviar -->
                    <button type="submit" id="btnLogin" class="btn-login-horizontal">
                        <span class="btn-spinner" aria-hidden="true"></span>
                        <span class="btn-text">Iniciar sesión</span>
                    </button>

                </form>

            </section>
        </div>
    </main>

    <!-- ── Lógica Frontend (Fetch asíncrono, feedback háptico y SweetAlert2) ── -->
    <script>
    (function () {
        'use strict';

        const BASE_URL          = '<?= $baseUrl ?>';
        const formLogin         = document.getElementById('formLogin');
        const loginCard         = document.getElementById('loginCard');
        const btnLogin          = document.getElementById('btnLogin');
        const inputCorreo       = document.getElementById('correo');
        const inputPassword     = document.getElementById('password');
        const btnTogglePassword = document.getElementById('btnTogglePassword');
        const iconEyeOpen       = document.getElementById('iconEyeOpen');
        const iconEyeClosed     = document.getElementById('iconEyeClosed');

        // Alternar visibilidad de contraseña
        btnTogglePassword.addEventListener('click', function () {
            const isPassword = inputPassword.type === 'password';
            inputPassword.type = isPassword ? 'text' : 'password';
            iconEyeOpen.style.display = isPassword ? 'none' : 'block';
            iconEyeClosed.style.display = isPassword ? 'block' : 'none';
            inputPassword.focus();
        });

        // Animación Shake estilo macOS al detectar error
        function triggerShake() {
            loginCard.classList.remove('shake');
            void loginCard.offsetWidth; // Forzar reflow para reiniciar la animación
            loginCard.classList.add('shake');
        }

        // Estado del botón durante envío
        function setLoadingState(loading) {
            btnLogin.disabled = loading;
            btnLogin.classList.toggle('is-loading', loading);
        }

        // Procesar formulario vía Fetch API
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
                        popup: 'apple-alert',
                        confirmButton: 'apple-confirm-btn'
                    },
                    buttonsStyling: false
                });
                return;
            }

            setLoadingState(true);

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
                    // Autenticación correcta
                    Swal.fire({
                        title: 'Acceso Concedido',
                        text: 'Iniciando sesión en Kredentia...',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1100,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'apple-alert'
                        }
                    }).then(() => {
                        window.location.href = data.redirigir || (BASE_URL + '/dashboard');
                    });
                } else {
                    // Credenciales incorrectas
                    triggerShake();
                    Swal.fire({
                        title: 'Acceso Denegado',
                        text: data.mensaje || 'Las credenciales proporcionadas no son válidas.',
                        icon: 'error',
                        confirmButtonText: 'Reintentar',
                        customClass: {
                            popup: 'apple-alert',
                            confirmButton: 'apple-confirm-btn'
                        },
                        buttonsStyling: false
                    });
                    setLoadingState(false);
                    inputPassword.value = '';
                    inputPassword.focus();
                }

            } catch (error) {
                console.error('[Kredentia Login Error]', error);
                triggerShake();
                Swal.fire({
                    title: 'Error de Conexión',
                    text: 'No fue posible comunicar con el servidor.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'apple-alert',
                        confirmButton: 'apple-confirm-btn'
                    },
                    buttonsStyling: false
                });
                setLoadingState(false);
            }
        });
    })();
    </script>
</body>
</html>
