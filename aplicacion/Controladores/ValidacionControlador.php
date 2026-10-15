<?php
declare(strict_types=1);

namespace App\Controladores;

use App\Modelos\FuncionarioModelo;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use Throwable;

/**
 * Clase ValidacionControlador
 *
 * Motor de validación y renderizado dinámico de certificados institucionales.
 * Procesa la lectura del código QR mediante token criptográfico y superpone
 * los datos del funcionario en la plantilla oficial PDF utilizando FPDI/FPDF.
 */
class ValidacionControlador
{
    private FuncionarioModelo $modelo;

    /**
     * Constructor del controlador.
     *
     * @param FuncionarioModelo|null $modelo Instancia del modelo de funcionarios.
     */
    public function __construct(?FuncionarioModelo $modelo = null)
    {
        $this->modelo = $modelo ?? new FuncionarioModelo();
    }

    /**
     * GET /certificado?id=CEDULA o GET /validar?t=TOKEN
     *
     * Valida el parámetro del código QR y genera en tiempo real el PDF del certificado institucional.
     */
    public function validar(): void
    {
        // 1. Capturar y sanear el identificador (cédula por ?id= o token por ?t=)
        $id    = trim((string) ($_GET['id'] ?? $_GET['cedula'] ?? ''));
        $token = trim((string) ($_GET['t'] ?? ''));

        if ($id === '' && $token === '') {
            $this->renderizarVistaError(
                titulo: 'Error: QR no válido',
                descripcion: 'El código QR escaneado no contiene un parámetro de autenticación o identificación válido.',
                codigoHttp: 400
            );
            return;
        }

        // 2. Consultar el funcionario en la base de datos (por cédula si viene por id= o por token si viene por t=)
        $funcionario = ($id !== '')
            ? $this->modelo->buscarPorCedula($id)
            : $this->modelo->buscarPorToken($token);

        if (!$funcionario) {
            $this->renderizarVistaError(
                titulo: 'Certificado no encontrado o revocado',
                descripcion: 'El documento que intenta validar no existe en los registros oficiales o ha sido revocado.',
                codigoHttp: 404
            );
            return;
        }

        // 3. Ruta a la plantilla base
        $rutaPlantilla = dirname(__DIR__, 2) . '/almacenamiento/plantillas_pdf/certificado_base.pdf';

        // 4. Procesamiento del PDF mediante FPDI y FPDF
        try {
            if (!file_exists($rutaPlantilla)) {
                throw new \RuntimeException('La plantilla base del certificado no existe en el almacenamiento del servidor.');
            }

            // Inicializar FPDI
            $pdf = new Fpdi();

            // Cargar archivo y contar páginas
            $totalPaginas = $pdf->setSourceFile($rutaPlantilla);
            if ($totalPaginas < 1) {
                throw new \RuntimeException('El archivo de plantilla PDF está dañado o no contiene páginas.');
            }

            // Importar página 1 de la plantilla base
            $templateId = $pdf->importPage(1);
            $tamano = $pdf->getTemplateSize($templateId);

            // Agregar página respetando la orientación y tamaño del documento original
            $pdf->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);

            // Dibujar la plantilla importada como fondo completo
            $pdf->useTemplate($templateId, 0, 0, $tamano['width'], $tamano['height'], true);

            // Configurar tipografía: Arial, Negrita, tamaño 26
            $pdf->SetFont('Arial', 'B', 26);

            // Color del texto: Azul oscuro institucional (RGB: 11, 44, 90)
            $pdf->SetTextColor(11, 44, 90);

            // Posición vertical aproximada en el centro de la hoja (ajustable según plantilla)
            $pdf->SetXY(0, 100);

            // Codificar caracteres para compatibilidad con fuentes estándar FPDF (ISO-8859-1)
            $nombreRaw = (string) ($funcionario['nombre_completo'] ?? '');
            $nombreCompleto = mb_convert_encoding($nombreRaw, 'ISO-8859-1', 'UTF-8');

            // Imprimir nombre completo del funcionario centrado en todo el ancho de la página
            $pdf->Cell($tamano['width'], 14, $nombreCompleto, 0, 1, 'C');

            // Limpiar cualquier búfer de salida previo para evitar corrupción del binario PDF
            if (ob_get_length()) {
                ob_end_clean();
            }

            // Enviar PDF al navegador para visualización inmediata en línea
            $cedula = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($funcionario['cedula'] ?? 'digital'));
            $pdf->Output('I', 'Certificado_' . $cedula . '.pdf');
            exit();

        } catch (CrossReferenceException $e) {
            error_log('[ValidacionControlador::validar CrossReferenceException] ' . $e->getMessage());
            $this->renderizarVistaError(
                titulo: 'Formato de Plantilla No Compatible',
                descripcion: 'La plantilla PDF utiliza compresión avanzada de referencias (PDF 1.5+). Debe guardarse en versión PDF 1.4 (Acrobat 5) para compatibilidad con el motor FPDI.',
                codigoHttp: 500
            );
        } catch (Throwable $e) {
            error_log('[ValidacionControlador::validar Error] ' . $e->getMessage());
            $this->renderizarVistaError(
                titulo: 'Error al Generar Certificado',
                descripcion: 'Ocurrió un problema técnico al renderizar el documento: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
                codigoHttp: 500
            );
        }
    }

    /**
     * Renderiza una vista minimalista y elegante inspirada en la interfaz iOS / Apple.
     *
     * @param string $titulo      Título principal del error o aviso.
     * @param string $descripcion Explicación legible para el usuario.
     * @param int    $codigoHttp  Código de estado HTTP para la respuesta.
     */
    private function renderizarVistaError(string $titulo, string $descripcion, int $codigoHttp = 400): void
    {
        http_response_code($codigoHttp);

        // Limpiar cualquier salida previa
        if (ob_get_length()) {
            ob_clean();
        }

        $tituloSeguro      = htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8');
        $descripcionSegura = htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8');

        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{$tituloSeguro} | ISPEB</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            background-color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #1d1d1f;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        .ios-card {
            max-width: 440px;
            width: 100%;
            background: #ffffff;
            border-radius: 28px;
            padding: 44px 32px 36px 32px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(0, 0, 0, 0.06);
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(8px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .icon-wrapper {
            width: 72px;
            height: 72px;
            margin: 0 auto 24px auto;
            border-radius: 50%;
            background: #fff1f2;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-wrapper svg {
            width: 36px;
            height: 36px;
            color: #e11d48;
        }

        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: #f1f5f9;
            color: #64748b;
            margin-bottom: 16px;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.025em;
            color: #0f172a;
            margin-bottom: 12px;
        }

        p {
            font-size: 15px;
            line-height: 1.55;
            color: #64748b;
            margin-bottom: 28px;
            font-weight: 400;
        }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin-bottom: 20px;
        }

        .institution-footer {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.01em;
        }

        .institution-footer span {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="ios-card">
        <div class="icon-wrapper">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.008v.008H12v-.008z" />
            </svg>
        </div>
        <div class="badge-status">Verificación Oficial</div>
        <h1>{$tituloSeguro}</h1>
        <p>{$descripcionSegura}</p>
        <div class="divider"></div>
        <div class="institution-footer">
            Instituto Superior de Policía del Estado Bolívar
            <span>Sistema Seguro de Certificación Institucional</span>
        </div>
    </div>
</body>
</html>
HTML;
        exit();
    }
}
