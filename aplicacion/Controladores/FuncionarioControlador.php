<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\FuncionarioModelo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Throwable;
use ZipArchive;

/**
 * Clase FuncionarioControlador
 *
 * Gestiona las acciones sobre los funcionarios institucionales:
 * listado, registro manual con generación de QR y carga masiva por Excel.
 */
class FuncionarioControlador
{
    /** Ruta base del proyecto para redirecciones */
    private const BASE_URL = '/docencia_qr';

    /** Directorio físico donde se almacenan los QRs generados */
    private const QR_DIR = STORAGE_DIR . '/qrs';

    /** URL pública base para el contenido del QR */
    private const URL_VALIDACION = 'http://localhost/docencia_qr/validar?t=';

    private FuncionarioModelo $modelo;

    /**
     * Constructor.
     *
     * @param FuncionarioModelo|null $modelo Instancia del modelo. Si es null, se crea automáticamente.
     */
    public function __construct(?FuncionarioModelo $modelo = null)
    {
        $this->modelo = $modelo ?? new FuncionarioModelo();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /descargar-qrs → Exportación masiva de QRs en formato ZIP
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Empaqueta todos los códigos QR (.png) generados en el almacenamiento
     * dentro de un archivo ZIP y fuerza la descarga en el navegador.
     */
    public function descargarQrsZip(): void
    {
        $this->requiereAuth();

        $directorioQrs = dirname(__DIR__, 2) . '/almacenamiento/qrs/';

        if (!is_dir($directorioQrs)) {
            mkdir($directorioQrs, 0755, true);
        }

        // Crear archivo temporal seguro en el directorio del servidor
        $zipTempPath = tempnam(sys_get_temp_dir(), 'ispeb_zip_');
        if ($zipTempPath === false) {
            http_response_code(500);
            die('[ISPEB Error] No se pudo crear el archivo temporal en el servidor.');
        }

        $zip = new ZipArchive();
        $res = $zip->open($zipTempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($res !== true) {
            @unlink($zipTempPath);
            http_response_code(500);
            die('[ISPEB Error] No se pudo inicializar el archivo ZIP.');
        }

        // Escanear el directorio de almacenamiento de QRs
        $archivos = scandir($directorioQrs);
        $archivosAnadidos = 0;

        if (is_array($archivos)) {
            foreach ($archivos as $archivo) {
                if ($archivo === '.' || $archivo === '..') {
                    continue;
                }

                $rutaCompleta = $directorioQrs . $archivo;

                // Añadir únicamente archivos que terminen en .png
                if (is_file($rutaCompleta) && str_ends_with(strtolower($archivo), '.png')) {
                    $zip->addFile($rutaCompleta, $archivo);
                    $archivosAnadidos++;
                }
            }
        }

        // Si no existen QRs generados aún, añadir un archivo explicativo
        if ($archivosAnadidos === 0) {
            $zip->addFromString(
                'LEEME.txt',
                "Sistema Institucional ISPEB\nNo se encontraron códigos QR generados (.png) en el directorio de almacenamiento."
            );
        }

        $zip->close();

        // Limpiar cualquier búfer de salida activo
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Cabeceras HTTP para forzar la descarga de QRs_ISPEB_Exportacion.zip
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="QRs_ISPEB_Exportacion.zip"');
        header('Content-Length: ' . (string) filesize($zipTempPath));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        // Transmitir archivo al cliente
        readfile($zipTempPath);

        // Eliminar el archivo temporal del servidor
        @unlink($zipTempPath);
        exit();
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /funcionarios → Listado (placeholder)
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Renderiza la vista de listado de funcionarios.
     * Protegida por sesión activa.
     */
    public function index(): void
    {
        $this->requiereAuth();

        $datos = $this->modelo->listarTodos();
        $funcionarios = $datos;
        require_once dirname(__DIR__) . '/Vistas/funcionarios.php';
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /registro-manual → Registro individual + generación QR
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Procesa el formulario de registro manual de un funcionario.
     *
     * Flujo:
     *  1. Valida sesión activa.
     *  2. Lee y sanea los datos POST.
     *  3. Genera un token SHA-256 único.
     *  4. Persiste el funcionario mediante FuncionarioModelo::registrar().
     *  5. Si el registro es exitoso, genera el PNG del QR con endroid/qr-code.
     *  6. Responde siempre con JSON { exito, mensaje [, qr_url] }.
     */
    public function registrarManual(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // ── Guardia de autenticación ─────────────────────────────────────
        if (!$this->sesionActiva()) {
            http_response_code(401);
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Sesión no autenticada. Por favor inicie sesión nuevamente.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // ── 1. Lectura y saneamiento de POST ─────────────────────────
            $cedula       = trim((string) ($_POST['cedula']       ?? ''));
            $nombre       = trim((string) ($_POST['nombre']       ?? ''));
            $cargo        = trim((string) ($_POST['cargo']        ?? ''));
            $departamento = trim((string) ($_POST['departamento'] ?? ''));

            // Validación mínima de campos requeridos
            if ($cedula === '' || $nombre === '' || $cargo === '' || $departamento === '') {
                http_response_code(422);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'Todos los campos son obligatorios.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── 2. Generación del token QR único ─────────────────────────
            $token = hash('sha256', $cedula . time() . 'ISPEB');

            // ── 3. Persistencia en base de datos ─────────────────────────
            $idRegistrador = (int) ($_SESSION['id_usuario'] ?? 0);
            $resultado = $this->modelo->registrar(
                $cedula,
                $nombre,
                $cargo,
                $departamento,
                $token,
                $idRegistrador
            );

            if (!$resultado['exito']) {
                // Cédula duplicada u otro error de BD
                $esDuplicado = str_contains($resultado['error'] ?? '', 'cédula');
                http_response_code($esDuplicado ? 409 : 500);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => $resultado['error'] ?? 'Error al guardar el funcionario.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── 4. Generación del QR con endroid/qr-code ─────────────────
            $contenidoQr   = self::URL_VALIDACION . $token;
            $cedulaSafe    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cedula);
            $rutaArchivoQr = self::QR_DIR . '/qr_' . $cedulaSafe . '.png';

            // Genera el PNG en disco Y obtiene el Data URI Base64 para el frontend
            $base64Uri = $this->generarQrPng($contenidoQr, $rutaArchivoQr);

            http_response_code(201);
            echo json_encode([
                'exito'      => true,
                'mensaje'    => "Funcionario '{$nombre}' registrado exitosamente.",
                'qr_base64'  => $base64Uri,
                'token'      => $token,
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioControlador::registrarManual Error] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Error interno del servidor al procesar el registro.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // GET /api/funcionario → Obtener detalles y código QR en Base64 vía AJAX
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Retorna los datos institucionales del funcionario y su código QR en Base64.
     * Permite al frontend visualizar la imagen incluso si /almacenamiento/qrs/
     * está protegido por .htaccess.
     */
    public function obtenerDetallesAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Validar sesión activa
        if (!$this->sesionActiva()) {
            http_response_code(401);
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Sesión no autenticada.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $cedula = trim((string) ($_GET['cedula'] ?? ''));

            if ($cedula === '') {
                http_response_code(400);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'El parámetro cédula es obligatorio.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Consultar datos del funcionario en BD
            $funcionario = $this->modelo->buscarPorCedula($cedula);

            if (!$funcionario) {
                http_response_code(404);
                echo json_encode([
                    'exito'   => false,
                    'mensaje' => 'Funcionario no encontrado con la cédula indicada.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Construcción de la ruta física del archivo QR
            $rutaQr     = dirname(__DIR__, 2) . '/almacenamiento/qrs/qr_' . $cedula . '.png';
            $cedulaSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cedula);
            $rutaQrSafe = dirname(__DIR__, 2) . '/almacenamiento/qrs/qr_' . $cedulaSafe . '.png';

            $qrBase64 = null;
            if (file_exists($rutaQr) && is_readable($rutaQr)) {
                $contenidoArchivo = file_get_contents($rutaQr);
                if ($contenidoArchivo !== false) {
                    $qrBase64 = 'data:image/png;base64,' . base64_encode($contenidoArchivo);
                }
            } elseif (file_exists($rutaQrSafe) && is_readable($rutaQrSafe)) {
                $contenidoArchivo = file_get_contents($rutaQrSafe);
                if ($contenidoArchivo !== false) {
                    $qrBase64 = 'data:image/png;base64,' . base64_encode($contenidoArchivo);
                }
            }

            http_response_code(200);
            echo json_encode([
                'exito'        => true,
                'cedula'       => $funcionario['cedula'] ?? $cedula,
                'nombre'       => $funcionario['nombre_completo'] ?? '',
                'cargo'        => $funcionario['cargo'] ?? '',
                'departamento' => $funcionario['departamento'] ?? '',
                'qr_base64'    => $qrBase64,
                'funcionario'  => [
                    'id_funcionario'  => $funcionario['id_funcionario'] ?? null,
                    'cedula'          => $funcionario['cedula'] ?? $cedula,
                    'nombre'          => $funcionario['nombre_completo'] ?? '',
                    'nombre_completo' => $funcionario['nombre_completo'] ?? '',
                    'cargo'           => $funcionario['cargo'] ?? '',
                    'departamento'    => $funcionario['departamento'] ?? '',
                    'fecha_registro'  => $funcionario['fecha_registro'] ?? '',
                ],
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioControlador::obtenerDetallesAjax Error] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'Error interno al consultar los detalles del funcionario.',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // POST /registro-excel → Carga masiva de funcionarios desde archivo .xlsx
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Procesa la carga masiva de funcionarios a partir de un archivo Excel (.xlsx).
     *
     * Flujo:
     *  1. Valida que se haya subido un archivo ($_FILES['documento_excel']) y que sea .xlsx.
     *  2. Carga el archivo temporal con \PhpOffice\PhpSpreadsheet\IOFactory::load().
     *  3. Lee la hoja activa recorriendo desde la fila 2 (A: Cédula, B: Nombre, C: Cargo, D: Departamento).
     *  4. Por cada fila válida genera token SHA-256, registra en BD y crea el archivo QR físico.
     *  5. Cuenta registros exitosos y duplicados/errores.
     *  6. Responde con JSON resumen: ['estado' => 'success', 'mensaje' => "..."].
     */
    public function procesarExcel(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Guardia de autenticación
        if (!$this->sesionActiva()) {
            http_response_code(401);
            echo json_encode([
                'estado'  => 'error',
                'mensaje' => 'Sesión expirada o no autenticada.',
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            // ── 1. Validación del archivo subido ─────────────────────────
            $excelFile = $_FILES['archivo_excel'] ?? $_FILES['documento_excel'] ?? null;
            if (empty($excelFile) || $excelFile['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode([
                    'estado'  => 'error',
                    'mensaje' => 'No se ha subido ningún archivo o ocurrió un error durante la carga.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $nombreOriginal = (string) $excelFile['name'];
            $extension      = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if ($extension !== 'xlsx') {
                http_response_code(400);
                echo json_encode([
                    'estado'  => 'error',
                    'mensaje' => 'Extensión no permitida. Solo se aceptan archivos de Excel con formato .xlsx.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            $archivoTemporal = (string) $excelFile['tmp_name'];

            if (!is_uploaded_file($archivoTemporal)) {
                http_response_code(400);
                echo json_encode([
                    'estado'  => 'error',
                    'mensaje' => 'El archivo temporal subido no es válido.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            // ── 2. Carga del Excel con PhpSpreadsheet ─────────────────────
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivoTemporal);
            $hoja        = $spreadsheet->getActiveSheet();
            $totalFilas  = $hoja->getHighestRow();

            $exitosos       = 0;
            $errores        = 0;
            $idRegistrador  = (int) ($_SESSION['id_usuario'] ?? 1);

            // ── 3. Iteración de filas desde la fila 2 ────────────────────
            for ($fila = 2; $fila <= $totalFilas; $fila++) {
                $cedula       = trim((string) ($hoja->getCell('A' . $fila)->getValue() ?? ''));
                $nombre       = trim((string) ($hoja->getCell('B' . $fila)->getValue() ?? ''));
                $cargo        = trim((string) ($hoja->getCell('C' . $fila)->getValue() ?? ''));
                $departamento = trim((string) ($hoja->getCell('D' . $fila)->getValue() ?? ''));

                // Si toda la fila está vacía, se ignora silenciosamente
                if ($cedula === '' && $nombre === '' && $cargo === '' && $departamento === '') {
                    continue;
                }

                // Si faltan campos requeridos en la fila, se cuenta como error
                if ($cedula === '' || $nombre === '' || $cargo === '' || $departamento === '') {
                    $errores++;
                    continue;
                }

                // Generar token único para el funcionario
                $token = hash('sha256', $cedula . time() . 'ISPEB');

                // Registro en base de datos
                $resultado = $this->modelo->registrar(
                    $cedula,
                    $nombre,
                    $cargo,
                    $departamento,
                    $token,
                    $idRegistrador
                );

                if (!empty($resultado['exito'])) {
                    // Generación del código QR físico en disco
                    $contenidoQr   = self::URL_VALIDACION . $token;
                    $cedulaSafe    = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cedula);
                    $rutaArchivoQr = self::QR_DIR . '/qr_' . $cedulaSafe . '.png';

                    $this->generarQrPng($contenidoQr, $rutaArchivoQr);
                    $exitosos++;
                } else {
                    $errores++;
                }
            }

            // ── 4. Respuesta de resumen ──────────────────────────────────
            http_response_code(200);
            echo json_encode([
                'estado'   => 'success',
                'mensaje'  => "Proceso completado. Registros exitosos: {$exitosos}, Errores/Duplicados: {$errores}",
                'exitosos' => $exitosos,
                'errores'  => $errores,
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioControlador::procesarExcel Error] %s', $e->getMessage()));
            http_response_code(500);
            echo json_encode([
                'estado'  => 'error',
                'mensaje' => 'Error al procesar el archivo Excel: ' . $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Métodos privados de apoyo
    // ────────────────────────────────────────────────────────────────────────

    /**
     * Genera y guarda un código QR en formato PNG usando endroid/qr-code v5.
     * Retorna el Data URI Base64 del PNG generado para ser embebido directamente
     * en el frontend sin depender de rutas web (evita bloqueos de .htaccess).
     *
     * @param string $contenido   Texto/URL que codifica el QR.
     * @param string $rutaArchivo Ruta absoluta donde se guarda el PNG.
     * @return string             Data URI listo para usar en src="..." o imageUrl de SweetAlert2.
     *
     * @throws \RuntimeException Si no se puede escribir el archivo.
     */
    private function generarQrPng(string $contenido, string $rutaArchivo): string
    {
        // Asegurar que el directorio destino exista
        $directorio = dirname($rutaArchivo);
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $qrCode = new QrCode($contenido);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Guardar copia física en disco (para uso interno / auditoría)
        file_put_contents($rutaArchivo, $result->getString());

        // Retornar Data URI Base64: se envía al frontend sin pasar por HTTP
        return $result->getDataUri();
    }

    /**
     * Verifica si existe una sesión de usuario autenticado activa.
     */
    private function sesionActiva(): bool
    {
        return !empty($_SESSION['auth']) && !empty($_SESSION['id_usuario']);
    }

    /**
     * Exige autenticación en sesión para continuar. Redirige a /login si no está autenticado.
     */
    protected function requiereAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!$this->sesionActiva() && !isset($_SESSION['rol'])) {
            $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;
            header('Location: ' . $base . '/login');
            exit();
        }
    }

    /**
     * GET /api/funcionarios/obtener?cedula=...
     * Retorna los datos de un funcionario en formato JSON para poblar el modal de edición.
     */
    public function obtenerAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->sesionActiva()) {
            http_response_code(401);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Sesión expirada.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $cedula = trim((string) ($_GET['cedula'] ?? ''));
        if ($cedula === '') {
            http_response_code(400);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Se requiere el número de cédula.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $funcionario = $this->modelo->buscarPorCedula($cedula);
        if (!$funcionario) {
            http_response_code(404);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Funcionario no encontrado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'estado'      => 'ok',
            'exito'       => true,
            'funcionario' => $funcionario,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * POST /api/funcionarios/actualizar
     * Actualiza los datos de un funcionario (permitido para cualquier rol autenticado).
     */
    public function actualizarAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->sesionActiva()) {
            http_response_code(401);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Sesión no autenticada.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $cedulaOriginal = trim((string) ($_POST['cedula_original'] ?? $_POST['cedula_actual'] ?? ''));
        $nuevaCedula    = trim((string) ($_POST['cedula'] ?? ''));
        $nombre         = trim((string) ($_POST['nombre'] ?? $_POST['nombre_completo'] ?? ''));
        $cargo          = trim((string) ($_POST['cargo'] ?? ''));
        $departamento   = trim((string) ($_POST['departamento'] ?? ''));

        if ($cedulaOriginal === '' || $nombre === '' || $cargo === '' || $departamento === '') {
            http_response_code(422);
            echo json_encode(['estado' => 'error', 'mensaje' => 'Todos los campos son obligatorios.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $res = $this->modelo->actualizar($cedulaOriginal, $nombre, $cargo, $departamento, $nuevaCedula);

        if (!$res['exito']) {
            http_response_code(409);
            echo json_encode(['estado' => 'error', 'mensaje' => $res['error'] ?? 'No se pudo actualizar el funcionario.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'estado'      => 'ok',
            'exito'       => true,
            'mensaje'     => 'Funcionario actualizado correctamente.',
            'funcionario' => [
                'cedula_original' => $cedulaOriginal,
                'cedula'          => !empty($nuevaCedula) ? $nuevaCedula : $cedulaOriginal,
                'nombre_completo' => $nombre,
                'cargo'           => $cargo,
                'departamento'    => $departamento
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
