<?php
declare(strict_types=1);

namespace App\Servicios;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Throwable;

/**
 * Servicio centralizado para la generación, almacenamiento y recuperación
 * resiliente de códigos QR institucionales.
 *
 * Diseñado con soporte de regeneración bajo demanda ("lazy generation")
 * para entornos en la nube con almacenamiento efímero (como Render, Docker, Railway).
 */
class QrServicio
{
    /**
     * Retorna el directorio físico donde residen los archivos PNG de los QRs.
     */
    public static function obtenerDirectorio(): string
    {
        $dir = defined('STORAGE_DIR')
            ? STORAGE_DIR . '/qrs'
            : dirname(__DIR__, 2) . '/almacenamiento/qrs';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Retorna la ruta absoluta del archivo PNG del código QR según la cédula.
     */
    public static function obtenerRutaArchivo(string $cedula): string
    {
        $dir = self::obtenerDirectorio();
        $cedulaSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', trim($cedula));
        return $dir . '/qr_' . $cedulaSafe . '.png';
    }

    /**
     * Resuelve la URL base pública de la aplicación de forma dinámica y canónica.
     */
    public static function obtenerBaseUrl(): string
    {
        $appUrl = getenv('APP_URL');
        if (!empty($appUrl) && (str_starts_with($appUrl, 'http://') || str_starts_with($appUrl, 'https://'))) {
            return rtrim($appUrl, '/');
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '/docencia_qr';
        if (str_starts_with($baseUrl, 'http://') || str_starts_with($baseUrl, 'https://')) {
            return rtrim($baseUrl, '/');
        }

        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host      = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return rtrim($protocolo . $host . $baseUrl, '/');
    }

    /**
     * Construye la URL de validación de certificado que codifica el QR.
     */
    public static function obtenerUrlValidacion(string $cedula): string
    {
        return self::obtenerBaseUrl() . '/certificado?id=' . trim($cedula);
    }

    /**
     * Garantiza que el archivo PNG del QR exista físicamente en disco.
     * Si no existe o está corrupto, lo regenera al vuelo inmediatamente.
     *
     * @param string $cedula Cédula del funcionario.
     * @return string Ruta absoluta del archivo PNG generado/verificado.
     */
    public static function asegurarQrPng(string $cedula): string
    {
        $rutaArchivo = self::obtenerRutaArchivo($cedula);

        if (!file_exists($rutaArchivo) || !is_readable($rutaArchivo) || filesize($rutaArchivo) === 0) {
            $urlValidacion = self::obtenerUrlValidacion($cedula);
            $qrCode = new QrCode($urlValidacion);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            file_put_contents($rutaArchivo, $result->getString());
        }

        return $rutaArchivo;
    }

    /**
     * Retorna el Data URI Base64 del código QR para ser embebido en el frontend (HTML/Modales/SweetAlert2).
     * Si el archivo físico no existía previamente, lo genera al vuelo.
     */
    public static function obtenerQrBase64(string $cedula): ?string
    {
        try {
            $rutaArchivo = self::asegurarQrPng($cedula);
            if (file_exists($rutaArchivo) && is_readable($rutaArchivo)) {
                $contenido = file_get_contents($rutaArchivo);
                if ($contenido !== false && strlen($contenido) > 0) {
                    return 'data:image/png;base64,' . base64_encode($contenido);
                }
            }
        } catch (Throwable $e) {
            error_log(sprintf('[QrServicio::obtenerQrBase64 Error] Cédula %s: %s', $cedula, $e->getMessage()));
        }

        return null;
    }
}
