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

        if (empty($_SESSION['auth']) || empty($_SESSION['id_usuario'])) {
            header('Location: ' . self::BASE_URL . '/');
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
}
