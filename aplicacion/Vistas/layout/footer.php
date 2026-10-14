<?php
/**
 * Plantilla Maestra de Pie de Página (Footer Layout) — ISPEB Sistema Institucional
 * Cierra las etiquetas de estructura global e inyecta constantes y scripts.
 *
 * Variables opcionales:
 * @var string|null $scriptsVista Bloque HTML/JS específico que la vista desee inyectar al final del DOM
 */

$baseUrl = defined('BASE_URL') 
    ? BASE_URL 
    : ((isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/docencia_qr')) ? '/docencia_qr' : '');
?>

    </div> <!-- /.main-wrapper -->

    <!-- ── Configuración Global de JavaScript ── -->
    <script>
        // Constante global de la ruta base accesible para cualquier petición Fetch API
        const BASE_URL = '<?= $baseUrl ?>';

        // Mixin Global: Notificaciones Toast estilo Apple (Liviano, sin backdrop ni bloqueo)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            // CRÍTICO: Forzar a que no exista backdrop u overlay oscuro
            backdrop: false, 
            showClass: {
                popup: 'animate__animated animate__fadeInRight'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutRight'
            },
            customClass: {
                popup: 'apple-toast'
            }
        });

        // Mixin Global: Alertas y Modales de Confirmación estilo Apple
        const AppleAlert = Swal.mixin({
            customClass: {
                popup: 'apple-popup',
                confirmButton: 'btn-apple-confirm',
                cancelButton: 'btn-apple-cancel'
            },
            buttonsStyling: false
        });

        /**
         * Diálogo de confirmación unificado para cierre de sesión con AppleAlert
         */
        function confirmarSalida(event) {
            if (event) {
                event.preventDefault();
            }

            AppleAlert.fire({
                title: '¿Cerrar Sesión?',
                text: 'Tendrás que ingresar tus credenciales nuevamente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = BASE_URL + '/logout';
                }
            });
        }
    </script>

    <!-- ── Espacio para Scripts Específicos de la Vista ── -->
    <?php if (!empty($scriptsVista)): ?>
        <?= $scriptsVista ?>
    <?php endif; ?>

</body>
</html>
