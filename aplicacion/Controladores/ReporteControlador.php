<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\FuncionarioModelo;
use FPDF;
use Throwable;

/**
 * Clase ReporteControlador
 *
 * Arquitectura Backend PHP 8 para generación de reportes y hojas de impresión PDF.
 * Procesa la segmentación de funcionarios y compone una cuadrícula de emisión masiva
 * de códigos QR institucionales con guías de corte y datos centrados mediante FPDF.
 */
class ReporteControlador
{
    private const BASE_URL = '/docencia_qr';
    private FuncionarioModelo $modelo;

    public function __construct(?FuncionarioModelo $modelo = null)
    {
        $this->modelo = $modelo ?? new FuncionarioModelo();
    }

    /**
     * GET /reportes → Renderiza la vista de Reportes y Filtros Avanzados.
     */
    public function index(): void
    {
        $this->requiereAuth();

        $vista = dirname(__DIR__) . '/Vistas/reportes.php';
        if (!file_exists($vista)) {
            http_response_code(500);
            die('[ISPEB] Vista de reportes no encontrada en: ' . $vista);
        }

        require_once $vista;
    }

    /**
     * GET /api/reportes/filtrar → Endpoint AJAX que retorna funcionarios filtrados en vivo (JSON).
     */
    public function filtrarAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->requiereAuth();

        $busqueda     = trim((string) ($_GET['busqueda'] ?? $_GET['texto'] ?? ''));
        $departamento = trim((string) ($_GET['departamento'] ?? $_GET['dept'] ?? ''));
        $cargo        = trim((string) ($_GET['cargo'] ?? ''));
        $rangoFechas  = $_GET['rango_fechas'] ?? $_GET['rango'] ?? $_GET['lote'] ?? null;
        $fechaDesde   = trim((string) ($_GET['fecha_desde'] ?? ''));
        $fechaHasta   = trim((string) ($_GET['fecha_hasta'] ?? ''));

        if (!empty($fechaDesde) || !empty($fechaHasta)) {
            $rangoFechas = [
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ];
        }

        // Tarea 2: Consulta al método dinámico del modelo incluyendo $cargo
        $resultados = $this->modelo->obtenerFiltrados($busqueda, $departamento, $cargo, $rangoFechas);

        // Respuesta JSON estructurada
        echo json_encode([
            'estado' => 'ok',
            'exito'  => true,
            'total'  => count($resultados),
            'datos'  => $resultados,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * GET /reportes/pdf → Tarea 1 y 2: Genera la Hoja de Impresión PDF en cuadrícula con FPDF.
     *
     * Recibe filtros vía $_GET:
     * - cedula, nombre, texto (búsqueda general)
     * - departamento, cargo
     * - lote (24h, semana, mes, personalizado)
     * - fecha_desde, fecha_hasta
     */
    public function generarHojaQRPdf(): void
    {
        // Silenciar avisos de deprecación o notices que puedan corromper los encabezados HTTP del PDF
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

        $this->requiereAuth();

        // ── 1. Capturar y sanear filtros de búsqueda desde $_GET ───────────
        $filtros = [
            'cedula'       => trim((string) ($_GET['cedula'] ?? '')),
            'nombre'       => trim((string) ($_GET['nombre'] ?? '')),
            'texto'        => trim((string) ($_GET['busqueda'] ?? $_GET['texto'] ?? '')),
            'departamento' => trim((string) ($_GET['dept'] ?? $_GET['departamento'] ?? '')),
            'cargo'        => trim((string) ($_GET['cargo'] ?? '')),
            'lote'         => trim((string) ($_GET['lote'] ?? 'todos')),
            'fecha_desde'  => trim((string) ($_GET['fecha_desde'] ?? '')),
            'fecha_hasta'  => trim((string) ($_GET['fecha_hasta'] ?? '')),
        ];

        // ── 2. Consultar modelo con filtros dinámicos ───────────────────────
        $funcionarios = $this->modelo->filtrar($filtros);

        // Si son 0 registros, retornar un error
        if (empty($funcionarios)) {
            http_response_code(404);
            $this->mostrarErrorSinRegistros();
            return;
        }

        // ── 3. Motor FPDF: Composición de Cuadrícula (3 columnas x 4 filas) ─
        try {
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(15, 12, 15);
            $pdf->SetAutoPageBreak(false); // Control manual de páginas para precisión milimétrica
            $pdf->SetTitle(mb_convert_encoding('Hoja de Impresión de QRs — ISPEB', 'ISO-8859-1', 'UTF-8'));
            $pdf->SetAuthor('Sistema Institucional ISPEB');
            $pdf->AddPage();

            // Parámetros de la Cuadrícula
            $columnasPorPagina = 3;
            $filasPorPagina    = 4;
            $limitePorHoja     = $columnasPorPagina * $filasPorPagina; // 12 credenciales

            $cardAncho = 58;  // 58 mm de ancho por credencial
            $cardAlto  = 60;  // 60 mm de alto por credencial
            $margenX   = 15;  // Margen izquierdo
            $margenY   = 24;  // Margen superior tras el encabezado
            $espacioX  = 6;   // Separación horizontal
            $espacioY  = 5;   // Separación vertical
            $qrSize    = 30;  // Tamaño del código QR (30x30 mm)

            $directorioQrs = defined('STORAGE_DIR') 
                ? STORAGE_DIR . '/qrs' 
                : dirname(__DIR__, 2) . '/almacenamiento/qrs';

            // Dibujar encabezado institucional en la primera página
            $this->dibujarEncabezadoPdf($pdf, count($funcionarios));

            $contador = 0;

            foreach ($funcionarios as $func) {
                // Cálculo de posición en la página actual (0 a 11)
                $posicionEnHoja = $contador % $limitePorHoja;

                // Si se alcanza el límite de 12 QRs, agregar una nueva página
                if ($contador > 0 && $posicionEnHoja === 0) {
                    $pdf->AddPage();
                    $this->dibujarEncabezadoPdf($pdf, count($funcionarios), true);
                }

                // Calcular columna (0, 1, 2) y fila (0, 1, 2, 3)
                $col = $posicionEnHoja % $columnasPorPagina;
                $row = (int) floor($posicionEnHoja / $columnasPorPagina);

                // Coordenadas absolutas milimétricas para esta credencial
                $x = $margenX + ($col * ($cardAncho + $espacioX));
                $y = $margenY + ($row * ($cardAlto + $espacioY));

                // ── A. Dibujar marco de recorte sutil (Guía de corte) ────────
                $pdf->SetDrawColor(215, 215, 220);
                $pdf->SetLineWidth(0.25);
                $pdf->Rect($x, $y, $cardAncho, $cardAlto, 'D');

                // ── B. Leer archivo físico de QR e insertar con $pdf->Image() ─
                $cedula = (string) ($func['cedula'] ?? '');
                $cedulaSegura = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cedula);
                $rutaArchivoQr = $directorioQrs . '/qr_' . $cedulaSegura . '.png';

                // Centrar horizontalmente el QR de 30mm en la tarjeta de 58mm
                $qrX = $x + (($cardAncho - $qrSize) / 2);
                $qrY = $y + 3;

                if (file_exists($rutaArchivoQr)) {
                    $pdf->Image($rutaArchivoQr, $qrX, $qrY, $qrSize, $qrSize);
                } else {
                    // Fallback visual si el QR no estuviese físicamente generado
                    $pdf->SetDrawColor(230, 230, 235);
                    $pdf->Rect($qrX, $qrY, $qrSize, $qrSize, 'D');
                    $pdf->SetFont('Arial', 'I', 7);
                    $pdf->SetTextColor(180, 180, 180);
                    $pdf->SetXY($x, $qrY + 12);
                    $pdf->Cell($cardAncho, 6, 'Sin archivo QR', 0, 0, 'C');
                }

                // ── C. Textos Justo Debajo del QR (Nombre, Cédula y Cargo) ────
                $nombreCompleto = (string) ($func['nombre_completo'] ?? '—');
                $cargo          = (string) ($func['cargo'] ?? '—');
                $departamento   = (string) ($func['departamento'] ?? '—');

                // 1. Nombre Completo (Negrita, centrado)
                $pdf->SetFont('Arial', 'B', 7.5);
                $pdf->SetTextColor(20, 20, 22);
                $pdf->SetXY($x + 2, $y + 35);
                $nombreTruncado = mb_strimwidth($nombreCompleto, 0, 26, '...');
                $pdf->Cell($cardAncho - 4, 4, mb_convert_encoding($nombreTruncado, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                // 2. Cédula de Identidad (Centrada)
                $pdf->SetFont('Arial', '', 7);
                $pdf->SetTextColor(70, 70, 75);
                $pdf->SetX($x + 2);
                $pdf->Cell($cardAncho - 4, 3.5, mb_convert_encoding('C.I. ' . $cedula, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                // 3. Cargo Institucional (Centrado)
                $pdf->SetFont('Arial', 'I', 6.5);
                $pdf->SetTextColor(110, 110, 115);
                $pdf->SetX($x + 2);
                $cargoTruncado = mb_strimwidth($cargo, 0, 28, '...');
                $pdf->Cell($cardAncho - 4, 3.5, mb_convert_encoding($cargoTruncado, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                // 4. Departamento de Adscripción
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetTextColor(130, 130, 135);
                $pdf->SetX($x + 2);
                $deptoTruncado = mb_strimwidth($departamento, 0, 30, '...');
                $pdf->Cell($cardAncho - 4, 3.5, mb_convert_encoding($deptoTruncado, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                $contador++;
            }

            // ── 4. Limpiar buffers y renderizar PDF directo en el navegador ──
            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf->Output('I', 'Hoja_QRs.pdf');
            exit();

        } catch (Throwable $e) {
            error_log(sprintf('[ReporteControlador::generarHojaQRPdf Error] %s', $e->getMessage()));
            http_response_code(500);
            die('[ISPEB Error] Fallo al componer el documento PDF con FPDF: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * Imprime el encabezado institucional oficial en cada página del PDF.
     */
    private function dibujarEncabezadoPdf(FPDF $pdf, int $totalRegistros, bool $esContinuacion = false): void
    {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(11, 44, 90); // Azul institucional ISPEB
        $titulo = $esContinuacion
            ? 'ISPEB — Hoja de Impresión de Credenciales QR (Continuación)'
            : 'INSTITUTO DE SALUD PÚBLICA DEL ESTADO BOLÍVAR (ISPEB)';
        $pdf->Cell(0, 6, mb_convert_encoding($titulo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(110, 110, 115);
        $subtitulo = sprintf(
            'Emisión: %s | Total: %d funcionarios | Rejilla estándar de corte (3×4)',
            date('d/m/Y H:i'),
            $totalRegistros
        );
        $pdf->Cell(0, 4.5, mb_convert_encoding($subtitulo, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(2);
    }

    /**
     * Despliega una respuesta de error amigable estilo Apple cuando no hay registros coincidentes.
     */
    private function mostrarErrorSinRegistros(): void
    {
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sin Registros Coincidentes — ISPEB</title>
    <style>
        body {
            margin: 0;
            padding: 40px 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f2f2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
        }
        .error-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 36px 32px;
            max-width: 440px;
            text-align: center;
            box-shadow: 0 16px 36px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.06);
        }
        .error-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: rgba(255, 149, 0, 0.12);
            color: #ff9500;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 18px auto;
        }
        h2 { margin: 0 0 10px 0; color: #1d1d1f; font-size: 1.25rem; font-weight: 700; }
        p { margin: 0 0 24px 0; color: #86868b; font-size: 0.90rem; line-height: 1.45; }
        .btn-return {
            display: inline-block;
            background: #0071e3;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">⚠️</div>
        <h2>Sin Registros Coincidentes</h2>
        <p>No se encontraron funcionarios registrados que coincidan con los filtros aplicados. Intenta ampliar el rango de fechas o los criterios de búsqueda.</p>
        <a href="javascript:history.back()" class="btn-return">Regresar a Filtros</a>
    </div>
</body>
</html>
HTML;
        exit();
    }

    /**
     * Guardia de autenticación institucional: confirma que el usuario tenga sesión activa.
     * Permite acceso pleno tanto a administradores como a usuarios con rol estándar.
     */
    private function requiereAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode([
                    'estado'  => 'error',
                    'exito'   => false,
                    'mensaje' => 'Sesión expirada. Por favor inicie sesión nuevamente.'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }

            header('Location: ' . self::BASE_URL . '/login');
            exit();
        }
    }
}
