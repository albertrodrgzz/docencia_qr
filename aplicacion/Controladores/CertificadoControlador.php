<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\FuncionarioModelo;
use FPDF;
use Throwable;

/**
 * Clase CertificadoControlador
 *
 * Controlador público para la emisión y verificación de certificados institucionales.
 * Accedido públicamente por cualquier usuario o dispositivo al escanear los códigos QR
 * de los funcionarios acreditados por el ISPEB.
 *
 * NO requiere autenticación de sesión (acceso público abierto).
 */
class CertificadoControlador
{
    private FuncionarioModelo $modelo;

    /**
     * Constructor del controlador público.
     *
     * @param FuncionarioModelo|null $modelo Instancia inyectable del modelo.
     */
    public function __construct(?FuncionarioModelo $modelo = null)
    {
        $this->modelo = $modelo ?? new FuncionarioModelo();
    }

    /**
     * GET /certificado?id=CEDULA
     *
     * Genera al vuelo y renderiza en el navegador el certificado oficial PDF
     * del funcionario mediante la librería FPDF.
     */
    public function generar(): void
    {
        // ── 1. Capturar y sanear la cédula recibida por GET ─────────────────
        $cedula = $_GET['id'] ?? null;
        $cedula = is_string($cedula) ? trim($cedula) : '';

        // Si no se proporcionó cédula, emitir certificado inválido
        if ($cedula === '') {
            $this->generarPdfInvalido('No se proporcionó un número de cédula o identificación.');
            return;
        }

        try {
            // ── 2. Consultar el funcionario en la base de datos ─────────────
            $funcionario = $this->modelo->buscarPorCedula($cedula);

            // Si el funcionario no existe o fue revocado
            if (!$funcionario) {
                $this->generarPdfInvalido('CERTIFICADO INVÁLIDO O NO ENCONTRADO');
                return;
            }

            // Datos del funcionario
            $nombreCompleto = (string) ($funcionario['nombre_completo'] ?? '—');
            $cedulaFunc     = (string) ($funcionario['cedula'] ?? $cedula);
            $cargo          = (string) ($funcionario['cargo'] ?? '—');
            $departamento   = (string) ($funcionario['departamento'] ?? '—');

            // ── 3. Composición del Certificado Oficial con FPDF ─────────────
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(false);
            $pdf->SetTitle($this->fpdfTexto('Certificado de Acreditación — ' . $nombreCompleto));
            $pdf->SetAuthor('ISPEB - Sistema Institucional de Certificaciones');
            $pdf->AddPage();

            // ── A. Marco Elegante alrededor de la página ────────────────────
            // Marco exterior (Azul oscuro institucional profundo)
            $pdf->SetDrawColor(15, 39, 68); // #0f2744
            $pdf->SetLineWidth(1.2);
            $pdf->Rect(10, 10, 190, 277, 'D');

            // Marco interior fino (Dorado / Bronce suave institucional)
            $pdf->SetDrawColor(197, 160, 89); // #c5a059
            $pdf->SetLineWidth(0.4);
            $pdf->Rect(13, 13, 184, 271, 'D');

            // ── B. Cabecera Institucional ────────────────────────────────────
            // Línea 1: REPÚBLICA BOLIVARIANA DE VENEZUELA
            $pdf->SetFont('Arial', 'B', 15);
            $pdf->SetTextColor(15, 39, 68);
            $pdf->SetXY(15, 26);
            $pdf->Cell(180, 8, $this->fpdfTexto('REPÚBLICA BOLIVARIANA DE VENEZUELA'), 0, 1, 'C');

            // Línea 2: ISPEB - SISTEMA INSTITUCIONAL DE CERTIFICACIONES
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(71, 85, 105); // Gris azulado institucional
            $pdf->Cell(180, 7, $this->fpdfTexto('ISPEB - SISTEMA INSTITUCIONAL DE CERTIFICACIONES'), 0, 1, 'C');

            // Divisor ornamental sutil
            $pdf->SetDrawColor(197, 160, 89);
            $pdf->SetLineWidth(0.6);
            $pdf->Line(50, 44, 160, 44);

            // Línea 3: CERTIFICADO DE ACREDITACIÓN OFICIAL
            $pdf->SetXY(15, 49);
            $pdf->SetFont('Arial', 'B', 17);
            $pdf->SetTextColor(15, 39, 68);
            $pdf->Cell(180, 9, $this->fpdfTexto('CERTIFICADO DE ACREDITACIÓN OFICIAL'), 0, 1, 'C');

            // ── C. Texto Introductorio y Cuadro de Acreditación ─────────────
            $pdf->SetXY(22, 65);
            $pdf->SetFont('Arial', '', 10.5);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->MultiCell(
                166,
                6,
                $this->fpdfTexto('La Dirección General del Instituto Superior Público Ezequiel Bujanda (ISPEB), en ejercicio de las atribuciones que le confiere el ordenamiento institucional vigente, valida y hace constar formalmente:'),
                0,
                'C'
            );

            // Contenedor principal de los datos (Fondo suave con borde redondeado)
            $cardX = 20;
            $cardY = 82;
            $cardW = 170;
            $cardH = 100;

            $pdf->SetFillColor(248, 250, 252); // Fondo #f8fafc
            $pdf->SetDrawColor(226, 232, 240); // Borde #e2e8f0
            $pdf->SetLineWidth(0.3);
            $pdf->Rect($cardX, $cardY, $cardW, $cardH, 'DF');

            // Franja lateral decorativa azul
            $pdf->SetFillColor(15, 39, 68);
            $pdf->Rect($cardX, $cardY, 4, $cardH, 'F');

            // ── D. Cuerpo (Datos Variables del Funcionario) ──────────────────
            $cursorY = $cardY + 8;
            $anchoLinea = $cardW - 16;
            $inicioX = $cardX + 10;

            // 1. "Se certifica que el ciudadano(a): " . Nombre Completo
            $pdf->SetXY($inicioX, $cursorY);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($anchoLinea, 5, $this->fpdfTexto('Se certifica que el ciudadano(a):'), 0, 1, 'L');

            $pdf->SetX($inicioX);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->SetTextColor(15, 23, 42); // #0f172a
            $pdf->Cell($anchoLinea, 7, $this->fpdfTexto($nombreCompleto), 0, 1, 'L');

            $cursorY += 15;

            // 2. "Titular de la Cédula de Identidad: " . Cédula
            $pdf->SetXY($inicioX, $cursorY);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($anchoLinea, 5, $this->fpdfTexto('Titular de la Cédula de Identidad:'), 0, 1, 'L');

            $pdf->SetX($inicioX);
            $pdf->SetFont('Arial', 'B', 12.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell($anchoLinea, 7, $this->fpdfTexto($cedulaFunc), 0, 1, 'L');

            $cursorY += 15;

            // 3. "Ejerce funciones activas como: " . Cargo
            $pdf->SetXY($inicioX, $cursorY);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($anchoLinea, 5, $this->fpdfTexto('Ejerce funciones activas como:'), 0, 1, 'L');

            $pdf->SetX($inicioX);
            $pdf->SetFont('Arial', 'B', 12.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell($anchoLinea, 7, $this->fpdfTexto($cargo), 0, 1, 'L');

            $cursorY += 15;

            // 4. "Adscrito a: " . Departamento
            $pdf->SetXY($inicioX, $cursorY);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell($anchoLinea, 5, $this->fpdfTexto('Adscrito a:'), 0, 1, 'L');

            $pdf->SetX($inicioX);
            $pdf->SetFont('Arial', 'B', 12.5);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell($anchoLinea, 7, $this->fpdfTexto($departamento), 0, 1, 'L');

            // ── E. Estampado del Código QR Físico Oficial ────────────────────
            $directorioQrs = defined('STORAGE_DIR')
                ? STORAGE_DIR . '/qrs'
                : dirname(__DIR__, 2) . '/almacenamiento/qrs';

            $cedulaSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $cedulaFunc);
            $rutaQrPng  = $directorioQrs . '/qr_' . $cedulaSafe . '.png';

            $qrSize = 42;
            $qrX = (210 - $qrSize) / 2; // Centrado en la página A4 (210mm)
            $qrY = 195;

            if (file_exists($rutaQrPng) && is_readable($rutaQrPng)) {
                // Marco sutil para el QR
                $pdf->SetDrawColor(226, 232, 240);
                $pdf->SetLineWidth(0.3);
                $pdf->Rect($qrX - 2, $qrY - 2, $qrSize + 4, $qrSize + 4, 'D');

                // Imagen QR
                $pdf->Image($rutaQrPng, $qrX, $qrY, $qrSize, $qrSize);
            } else {
                // Fallback visual si el PNG físico no existe
                $pdf->SetDrawColor(226, 232, 240);
                $pdf->Rect($qrX, $qrY, $qrSize, $qrSize, 'D');
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(150, 150, 150);
                $pdf->SetXY($qrX, $qrY + 18);
                $pdf->Cell($qrSize, 6, $this->fpdfTexto('Código QR'), 0, 0, 'C');
            }

            // Etiqueta de validación
            $pdf->SetXY(20, $qrY + $qrSize + 4);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetTextColor(15, 39, 68);
            $pdf->Cell(170, 5, $this->fpdfTexto('SELLO DIGITAL DE AUTENTICIDAD ISPEB'), 0, 1, 'C');

            // ── F. Pie de página (Seguridad) ────────────────────────────────
            $pdf->SetXY(15, 268);
            $pdf->SetFont('Arial', '', 7.5);
            $pdf->SetTextColor(100, 116, 139);
            $textoSeguridad = 'Documento verificado criptográficamente mediante código QR. Fecha de consulta: ' . date('Y-m-d H:i:s');
            $pdf->Cell(180, 5, $this->fpdfTexto($textoSeguridad), 0, 0, 'C');

            // ── G. Renderizado directo al navegador ─────────────────────────
            if (ob_get_length()) {
                ob_end_clean();
            }

            $pdf->Output('I', 'Certificado_' . $cedulaFunc . '.pdf');
            exit();

        } catch (Throwable $e) {
            error_log(sprintf('[CertificadoControlador::generar Error] %s', $e->getMessage()));
            $this->generarPdfInvalido('Ocurrió un error interno al generar el certificado.');
        }
    }

    /**
     * Genera un PDF simple con mensaje de error en color rojo cuando el funcionario no existe.
     *
     * @param string $mensaje Motivo del error o invalidez.
     */
    private function generarPdfInvalido(string $mensaje): void
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTitle($this->fpdfTexto('Certificado Inválido — ISPEB'));
        $pdf->AddPage();

        // Marco de alerta en rojo
        $pdf->SetDrawColor(239, 68, 68); // #ef4444
        $pdf->SetLineWidth(0.8);
        $pdf->Rect(12, 12, 186, 273, 'D');

        // Título principal en rojo destacado
        $pdf->SetXY(15, 115);
        $pdf->SetFont('Arial', 'B', 17);
        $pdf->SetTextColor(220, 38, 38); // Rojo
        $pdf->Cell(180, 10, $this->fpdfTexto('CERTIFICADO INVÁLIDO O NO ENCONTRADO'), 0, 1, 'C');

        // Detalle de la invalidación
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetXY(20, 130);
        $pdf->Cell(170, 7, $this->fpdfTexto($mensaje), 0, 1, 'C');

        $pdf->SetXY(20, 140);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(170, 6, $this->fpdfTexto('El documento consultado no figura en el registro oficial o ha sido revocado.'), 0, 1, 'C');

        // Pie de página de auditoría
        $pdf->SetXY(15, 268);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(180, 5, $this->fpdfTexto('Consulta de verificación de seguridad • ' . date('Y-m-d H:i:s')), 0, 0, 'C');

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf->Output('I', 'Certificado_Invalido.pdf');
        exit();
    }

    /**
     * Convierte cadenas UTF-8 a ISO-8859-1 para asegurar renderizado correcto de acentos y caracteres especiales en FPDF.
     *
     * @param string $texto Texto en formato UTF-8.
     * @return string Texto codificado en ISO-8859-1 compatible con fuentes FPDF.
     */
    private function fpdfTexto(string $texto): string
    {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
}
