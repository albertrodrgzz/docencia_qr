<?php
declare(strict_types=1);

namespace App\Controladores;

use Configuracion\ConexionBD;
use PDO;
use Throwable;

/**
 * Clase BackupControlador
 *
 * Gestiona la exportación y respaldo completo de la base de datos institucional.
 * Genera dinámicamente un volcado SQL (DDL + DML) y fuerza su descarga en el navegador.
 */
class BackupControlador
{
    private const BASE_URL = '/docencia_qr';

    /**
     * GET /reportes → Renderiza la vista de Reportes y Respaldos.
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . $base . '/login');
            exit();
        }

        // RBAC: Solo administradores pueden acceder a Reportes y Respaldos
        $rol = strtolower(trim((string) ($_SESSION['rol'] ?? '')));
        if ($rol !== 'admin' && $rol !== 'administrador') {
            header('Location: ' . $base . '/dashboard');
            exit();
        }

        $vista = APP_DIR . '/Vistas/reportes.php';
        if (!file_exists($vista)) {
            http_response_code(500);
            die('[ISPEB] Vista de reportes no encontrada en: ' . $vista);
        }

        require $vista;
    }

    /**
     * GET /respaldo-bd
     *
     * Realiza un respaldo integral de la base de datos (estructura y registros).
     */
    public function respaldar(): void
    {
        // 1. Guardia de autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . $base . '/login');
            exit();
        }

        try {
            // 2. Obtener la conexión PDO mediante el Singleton institucional
            $pdo = ConexionBD::getInstancia();

            // 3. Encabezado del script SQL de respaldo
            $fechaHora = date('Y-m-d H:i:s');
            $sqlDump = <<<SQL
-- =============================================================================
-- Sistema de Certificación Institucional - ISPEB
-- Respaldo de Base de Datos
-- Generado el: {$fechaHora}
-- Versión PHP: 8+ | Motor: MySQL / MariaDB (PDO Dump)
-- =============================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


SQL;

            // 4. Obtener listado de todas las tablas en la base de datos
            $stmtTablas = $pdo->query('SHOW TABLES');
            $tablas = $stmtTablas->fetchAll(PDO::FETCH_COLUMN);

            if (empty($tablas)) {
                $sqlDump .= "-- No se encontraron tablas en la base de datos.\n";
            }

            foreach ($tablas as $tabla) {
                $nombreTablaSeguro = str_replace('`', '``', (string) $tabla);

                $sqlDump .= "\n-- -------------------------------------------------------------\n";
                $sqlDump .= "-- Estructura y datos de la tabla `{$nombreTablaSeguro}`\n";
                $sqlDump .= "-- -------------------------------------------------------------\n\n";

                // Eliminar tabla previa para evitar colisiones
                $sqlDump .= "DROP TABLE IF EXISTS `{$nombreTablaSeguro}`;\n";

                // Estructura DDL (CREATE TABLE)
                $stmtCreate = $pdo->query("SHOW CREATE TABLE `{$nombreTablaSeguro}`");
                $createRow = $stmtCreate->fetch(PDO::FETCH_NUM);

                if (!empty($createRow[1])) {
                    $sqlDump .= $createRow[1] . ";\n\n";
                }

                // Datos DML (INSERT INTO)
                $stmtDatos = $pdo->query("SELECT * FROM `{$nombreTablaSeguro}`");
                $filas = $stmtDatos->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($filas)) {
                    // Obtener nombres de columnas
                    $columnas = array_keys($filas[0]);
                    $columnasEscapadas = array_map(fn($col) => '`' . str_replace('`', '``', (string) $col) . '`', $columnas);
                    $listaColumnas = implode(', ', $columnasEscapadas);

                    $sqlDump .= "INSERT INTO `{$nombreTablaSeguro}` ({$listaColumnas}) VALUES\n";

                    $totalFilas = count($filas);
                    $i = 0;

                    foreach ($filas as $fila) {
                        $i++;
                        $valoresEscapados = [];

                        foreach ($fila as $valor) {
                            if ($valor === null) {
                                $valoresEscapados[] = 'NULL';
                            } else {
                                // Escapado seguro con el método nativo de PDO
                                $valoresEscapados[] = $pdo->quote((string) $valor);
                            }
                        }

                        $linea = '  (' . implode(', ', $valoresEscapados) . ')';
                        $linea .= ($i === $totalFilas) ? ";\n\n" : ",\n";
                        $sqlDump .= $linea;
                    }
                }
            }

            // 5. Cierre y finalización del script
            $sqlDump .= <<<SQL
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

-- =============================================================================
-- Fin del Respaldo ISPEB
-- =============================================================================
SQL;

            // 6. Limpiar cualquier búfer de salida activo
            if (ob_get_length()) {
                ob_end_clean();
            }

            // 7. Cabeceras HTTP para forzar la descarga del archivo SQL
            $nombreArchivo = 'backup_ispeb_bd_' . date('Y-m-d') . '.sql';

            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
            header('Content-Length: ' . (string) strlen($sqlDump));
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            echo $sqlDump;
            exit();

        } catch (Throwable $e) {
            error_log('[BackupControlador::respaldar Error] ' . $e->getMessage());
            http_response_code(500);
            die('[ISPEB Error] Ocurrió un fallo al generar el respaldo de la base de datos: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * GET /reportes/pdf → Genera una hoja de impresión estructurada en PDF con múltiples códigos QR y datos.
     */
    public function generarPdfQrs(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $base = defined('BASE_URL') ? BASE_URL : self::BASE_URL;

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . $base . '/login');
            exit();
        }

        // Filtros opcionales
        $texto        = trim((string) ($_GET['texto'] ?? ''));
        $departamento = trim((string) ($_GET['departamento'] ?? ''));
        $cargo        = trim((string) ($_GET['cargo'] ?? ''));
        $lote         = trim((string) ($_GET['lote'] ?? 'todos'));
        $fechaDesde   = trim((string) ($_GET['fecha_desde'] ?? ''));
        $fechaHasta   = trim((string) ($_GET['fecha_hasta'] ?? ''));

        $pdo = ConexionBD::getInstancia();

        $query = "SELECT cedula, nombre_completo, cargo, departamento, creado_en FROM funcionarios_registrados WHERE 1=1";
        $params = [];

        if ($texto !== '') {
            $query .= " AND (cedula LIKE :texto OR nombre_completo LIKE :texto)";
            $params[':texto'] = '%' . $texto . '%';
        }

        if ($departamento !== '') {
            $query .= " AND departamento = :depto";
            $params[':depto'] = $departamento;
        }

        if ($cargo !== '') {
            $query .= " AND cargo = :cargo";
            $params[':cargo'] = $cargo;
        }

        if ($lote === '24h') {
            $query .= " AND creado_en >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        } elseif ($lote === 'semana') {
            $query .= " AND creado_en >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($lote === 'mes') {
            $query .= " AND creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } elseif ($lote === 'personalizado') {
            if ($fechaDesde !== '') {
                $query .= " AND creado_en >= :fecha_desde";
                $params[':fecha_desde'] = $fechaDesde . ' 00:00:00';
            }
            if ($fechaHasta !== '') {
                $query .= " AND creado_en <= :fecha_hasta";
                $params[':fecha_hasta'] = $fechaHasta . ' 23:59:59';
            }
        }

        $query .= " ORDER BY nombre_completo ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Silenciar avisos de deprecación para no corromper la salida binaria del PDF
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

        // Instanciar FPDF
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTitle(mb_convert_encoding('Hoja de Impresión QRs — ISPEB', 'ISO-8859-1', 'UTF-8'));
        $pdf->AddPage();

        // Encabezado institucional
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(11, 44, 90);
        $pdf->Cell(0, 6, mb_convert_encoding('INSTITUTO DE SALUD PÚBLICA DEL ESTADO BOLÍVAR (ISPEB)', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->SetTextColor(110, 110, 115);
        $pdf->Cell(0, 5, mb_convert_encoding('Hoja de Emisión de Credenciales QR para Recorte | Emisión: ' . date('d/m/Y H:i') . ' | Total: ' . count($funcionarios) . ' funcionarios', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $pdf->Ln(4);

        if (empty($funcionarios)) {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(140, 140, 140);
            $pdf->Cell(0, 30, mb_convert_encoding('No se encontraron funcionarios registrados con los filtros seleccionados.', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            $pdf->Output('I', 'Hoja_Impresion_QRs.pdf');
            exit();
        }

        // Layout de rejilla: 3 columnas x 4 filas por página = 12 tarjetas
        $cols   = 3;
        $cardW  = 58;
        $cardH  = 61;
        $startX = 15;
        $startY = 24;
        $gapX   = 6;
        $gapY   = 5;

        $i = 0;
        foreach ($funcionarios as $func) {
            $pageIndex = $i % 12;
            if ($i > 0 && $pageIndex === 0) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor(11, 44, 90);
                $pdf->Cell(0, 5, mb_convert_encoding('ISPEB - Hoja de Impresión de Credenciales QR (Continuación)', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                $pdf->Ln(4);
            }

            $col = $pageIndex % $cols;
            $row = (int) floor($pageIndex / $cols);

            $x = $startX + ($col * ($cardW + $gapX));
            $y = $startY + ($row * ($cardH + $gapY));

            // Marco con borde sutil de recorte
            $pdf->SetDrawColor(215, 215, 220);
            $pdf->SetLineWidth(0.3);
            $pdf->Rect($x, $y, $cardW, $cardH, 'D');

            // Cargar imagen QR si existe
            $cedulaSafe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $func['cedula']);
            $qrPath = STORAGE_DIR . '/qrs/qr_' . $cedulaSafe . '.png';

            if (file_exists($qrPath)) {
                $pdf->Image($qrPath, $x + ($cardW - 30) / 2, $y + 3, 30, 30);
            } else {
                $pdf->SetFont('Arial', 'I', 7);
                $pdf->SetTextColor(180, 180, 180);
                $pdf->SetXY($x, $y + 14);
                $pdf->Cell($cardW, 6, 'Sin QR generado', 0, 0, 'C');
            }

            // Datos institucionales del Funcionario
            $pdf->SetTextColor(25, 25, 28);
            $pdf->SetFont('Arial', 'B', 7.5);
            $pdf->SetXY($x + 2, $y + 35);
            $nombreCorto = mb_strimwidth((string)$func['nombre_completo'], 0, 26, '...');
            $pdf->Cell($cardW - 4, 4, mb_convert_encoding($nombreCorto, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(70, 70, 75);
            $pdf->SetX($x + 2);
            $pdf->Cell($cardW - 4, 3.5, mb_convert_encoding('C.I. ' . $func['cedula'], 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

            $pdf->SetFont('Arial', 'I', 6.5);
            $pdf->SetTextColor(110, 110, 115);
            $pdf->SetX($x + 2);
            $cargoCorto = mb_strimwidth((string)$func['cargo'], 0, 28, '...');
            $pdf->Cell($cardW - 4, 3.5, mb_convert_encoding($cargoCorto, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

            $deptoCorto = mb_strimwidth((string)$func['departamento'], 0, 30, '...');
            $pdf->SetX($x + 2);
            $pdf->Cell($cardW - 4, 3.5, mb_convert_encoding($deptoCorto, 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

            $i++;
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdf->Output('I', 'Hoja_Impresion_QRs_ISPEB.pdf');
        exit();
    }
}
