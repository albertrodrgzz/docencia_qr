<?php
declare(strict_types=1);

namespace App\Modelos;

use Configuracion\ConexionBD;
use PDO;
use PDOException;
use Throwable;

/**
 * Clase FuncionarioModelo
 * 
 * Gestiona la persistencia y consultas de datos sobre los funcionarios institucionales.
 */
class FuncionarioModelo
{
    private PDO $pdo;

    /**
     * Constructor del modelo.
     * 
     * @param PDO|null $pdo Instancia de conexión PDO. Si es null, inicializa la conexión Singleton.
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConexionBD::getInstancia();
    }

    /**
     * Registra un nuevo funcionario en la base de datos.
     * 
     * @param string     $cedula         Cédula de identidad del funcionario.
     * @param string     $nombre         Nombre completo del funcionario.
     * @param string     $cargo          Cargo o puesto institucional.
     * @param string     $departamento   Departamento o unidad de adscripción.
     * @param string     $token_qr       Token único para la validación y generación del código QR.
     * @param int|string $id_registrador ID del usuario del sistema que efectúa el registro.
     * 
     * @return array{exito: bool, error?: string} Resultado de la operación.
     */
    public function registrar(
        string $cedula,
        string $nombre,
        string $cargo,
        string $departamento,
        string $token_qr,
        int|string $id_registrador
    ): array {
        $sql = "INSERT INTO funcionarios_registrados 
                (cedula, nombre_completo, cargo, departamento, token_qr, registrado_por) 
                VALUES (:cedula, :nombre_completo, :cargo, :departamento, :token_qr, :registrado_por)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cedula'          => trim($cedula),
                ':nombre_completo' => trim($nombre),
                ':cargo'           => trim($cargo),
                ':departamento'    => trim($departamento),
                ':token_qr'        => trim($token_qr),
                ':registrado_por'  => (int) $id_registrador,
            ]);

            return ['exito' => true];
        } catch (PDOException $e) {
            // Detección de duplicidad de clave única (SQLSTATE 23000 o código MySQL 1062)
            if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? 0) === 1062) {
                return [
                    'exito' => false,
                    'error' => 'La cédula ya está registrada'
                ];
            }

            error_log(sprintf('[FuncionarioModelo::registrar Error PDO] %s', $e->getMessage()));
            return [
                'exito' => false,
                'error' => 'Error al registrar el funcionario en la base de datos.'
            ];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::registrar Error General] %s', $e->getMessage()));
            return [
                'exito' => false,
                'error' => 'Ocurrió un error inesperado al procesar el registro.'
            ];
        }
    }

    /**
     * Busca un funcionario registrado mediante su token único de validación QR.
     *
     * @param string $token Token único asociado al funcionario.
     * @return array<string, mixed>|false Datos del funcionario en arreglo asociativo, o false si no existe.
     */
    public function buscarPorToken(string $token): array|false
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        $sql = "SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, registrado_por, fecha_registro
                FROM funcionarios_registrados 
                WHERE token_qr = :token 
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            return $funcionario ?: false;
        } catch (PDOException $e) {
            error_log(sprintf('[FuncionarioModelo::buscarPorToken Error PDO] %s', $e->getMessage()));
            return false;
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::buscarPorToken Error General] %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Busca un funcionario registrado por su número de cédula de identidad.
     *
     * @param string $cedula Cédula del funcionario.
     * @return array<string, mixed>|false Datos del funcionario o false si no existe.
     */
    public function buscarPorCedula(string $cedula): array|false
    {
        $cedula = trim($cedula);
        if ($cedula === '') {
            return false;
        }

        $sql = "SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, registrado_por, fecha_registro
                FROM funcionarios_registrados 
                WHERE cedula = :cedula 
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':cedula' => $cedula]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            return $funcionario ?: false;
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::buscarPorCedula Error] %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Obtiene el listado completo de funcionarios registrados ordenados por ID descendente.
     *
     * @return array<int, array<string, mixed>> Lista de funcionarios.
     */
    public function listarTodos(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, registrado_por, fecha_registro 
                 FROM funcionarios_registrados 
                 ORDER BY id_funcionario DESC"
            );
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $registros ?: [];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::listarTodos Error] %s', $e->getMessage()));
            return [];
        }
    }

    /**
     * Obtiene estadísticas generales del sistema institucional:
     * - Total de funcionarios registrados
     * - Total registrados en la fecha actual (hoy)
     * - Los últimos 5 funcionarios registrados
     *
     * @return array{
     *     total_funcionarios: int,
     *     registrados_hoy: int,
     *     ultimos_funcionarios: array<int, array<string, mixed>>
     * } Arreglo asociativo con las métricas y los últimos registros.
     */
    public function obtenerEstadisticas(): array
    {
        try {
            // 1. Total general de funcionarios
            $stmtTotal = $this->pdo->query("SELECT COUNT(*) FROM funcionarios_registrados");
            $totalFuncionarios = (int) $stmtTotal->fetchColumn();

            // 2. Total registrados hoy
            $stmtHoy = $this->pdo->query("SELECT COUNT(*) FROM funcionarios_registrados WHERE DATE(fecha_registro) = CURDATE()");
            $registradosHoy = (int) $stmtHoy->fetchColumn();

            // 3. Últimos 5 funcionarios registrados
            $stmtUltimos = $this->pdo->query(
                "SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, fecha_registro 
                 FROM funcionarios_registrados 
                 ORDER BY id_funcionario DESC 
                 LIMIT 5"
            );
            $ultimosFuncionarios = $stmtUltimos->fetchAll(PDO::FETCH_ASSOC);

            return [
                'total_funcionarios'   => $totalFuncionarios,
                'registrados_hoy'      => $registradosHoy,
                'ultimos_funcionarios' => $ultimosFuncionarios ?: [],
            ];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::obtenerEstadisticas Error] %s', $e->getMessage()));
            return [
                'total_funcionarios'   => 0,
                'registrados_hoy'      => 0,
                'ultimos_funcionarios' => [],
            ];
        }
    }

    /**
     * Filtra los funcionarios registrados según criterios opcionales (WHERE 1=1 dinámico).
     *
     * @param array{
     *     cedula?: string,
     *     nombre?: string,
     *     texto?: string,
     *     departamento?: string,
     *     cargo?: string,
     *     lote?: string,
     *     fecha_desde?: string,
     *     fecha_hasta?: string
     * } $filtros Filtros opcionales pasados vía GET/POST.
     * @return array<int, array<string, mixed>> Registros de funcionarios coincidentes.
     */
    public function filtrar(array $filtros = []): array
    {
        try {
            $sql = "SELECT id_funcionario, cedula, nombre_completo, cargo, departamento, token_qr, fecha_registro 
                    FROM funcionarios_registrados 
                    WHERE 1=1";
            $params = [];

            // 1. Filtro por Cédula
            if (!empty($filtros['cedula'])) {
                $sql .= " AND cedula LIKE :cedula";
                $params[':cedula'] = '%' . trim((string) $filtros['cedula']) . '%';
            }

            // 2. Filtro por Nombre Completo
            if (!empty($filtros['nombre'])) {
                $sql .= " AND nombre_completo LIKE :nombre";
                $params[':nombre'] = '%' . trim((string) $filtros['nombre']) . '%';
            }

            // 3. Búsqueda combinada por Texto (Cédula o Nombre)
            if (!empty($filtros['texto'])) {
                $sql .= " AND (cedula LIKE :texto_cedula OR nombre_completo LIKE :texto_nombre)";
                $valTexto = '%' . trim((string) $filtros['texto']) . '%';
                $params[':texto_cedula'] = $valTexto;
                $params[':texto_nombre'] = $valTexto;
            }

            // 4. Filtro por Departamento
            if (!empty($filtros['departamento'])) {
                $sql .= " AND departamento = :departamento";
                $params[':departamento'] = trim((string) $filtros['departamento']);
            }

            // 5. Filtro por Cargo
            if (!empty($filtros['cargo'])) {
                $sql .= " AND cargo = :cargo";
                $params[':cargo'] = trim((string) $filtros['cargo']);
            }

            // 6. Filtro por Lote Temporal
            $lote = trim((string) ($filtros['lote'] ?? ''));
            if ($lote === '24h') {
                $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            } elseif ($lote === 'semana') {
                $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            } elseif ($lote === 'mes') {
                $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            } elseif ($lote === 'personalizado' || (!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']))) {
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND fecha_registro >= :fecha_desde";
                    $params[':fecha_desde'] = trim((string) $filtros['fecha_desde']) . ' 00:00:00';
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND fecha_registro <= :fecha_hasta";
                    $params[':fecha_hasta'] = trim((string) $filtros['fecha_hasta']) . ' 23:59:59';
                }
            }

            $sql .= " ORDER BY nombre_completo ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::filtrar Error] %s', $e->getMessage()));
            return [];
        }
    }

    /**
     * Motor de búsqueda y filtrado dinámico para el módulo de Reportes.
     *
     * Construye una consulta SQL dinámica y segura con PDO:
     * - Si $busqueda no está vacío, concatena AND (cedula LIKE :busqueda_cedula OR nombre_completo LIKE :busqueda_nombre)
     * - Si $departamento no está vacío, concatena AND departamento = :dept
     * - Si $rangoFechas está presente, concatena el filtro temporal correspondiente
     *
     * @param string|null $busqueda Cédula o nombre completo del funcionario.
     * @param string|null $departamento Departamento institucional asignado.
     * @param mixed $rangoFechas Lote ('24h', 'semana', 'mes') o array asociativo con 'desde' y 'hasta'.
     * @return array<int, array<string, mixed>> Lista de funcionarios encontrados.
     */
    public function obtenerFiltrados(
        ?string $busqueda = null,
        ?string $departamento = null,
        ?string $cargo = null,
        mixed $rangoFechas = null
    ): array {
        try {
            $sql = "SELECT * FROM funcionarios_registrados WHERE 1=1";
            $params = [];

            // 1. Filtro dinámico por búsqueda de texto (Cédula o Nombre)
            $busqueda = trim((string) $busqueda);
            if ($busqueda !== '') {
                $sql .= " AND (cedula LIKE :busqueda_cedula OR nombre_completo LIKE :busqueda_nombre)";
                $paramBusqueda = '%' . $busqueda . '%';
                $params[':busqueda_cedula'] = $paramBusqueda;
                $params[':busqueda_nombre'] = $paramBusqueda;
            }

            // 2. Filtro dinámico por Departamento
            $departamento = trim((string) $departamento);
            if ($departamento !== '' && $departamento !== 'todos') {
                $sql .= " AND departamento = :dept";
                $params[':dept'] = $departamento;
            }

            // 3. Filtro dinámico por Cargo Institucional (Tarea 3)
            $cargo = trim((string) $cargo);
            if (!empty($cargo) && $cargo !== 'todos') {
                $sql .= " AND cargo = :cargo";
                $params[':cargo'] = $cargo;
            }

            // 4. Filtro dinámico por Rango de Fechas / Lote temporal
            if (!empty($rangoFechas)) {
                if (is_array($rangoFechas)) {
                    $desde = trim((string) ($rangoFechas['desde'] ?? $rangoFechas['fecha_desde'] ?? ''));
                    $hasta = trim((string) ($rangoFechas['hasta'] ?? $rangoFechas['fecha_hasta'] ?? ''));

                    if ($desde !== '') {
                        $sql .= " AND fecha_registro >= :fecha_desde";
                        $params[':fecha_desde'] = $desde . ' 00:00:00';
                    }
                    if ($hasta !== '') {
                        $sql .= " AND fecha_registro <= :fecha_hasta";
                        $params[':fecha_hasta'] = $hasta . ' 23:59:59';
                    }
                } else {
                    $lote = strtolower(trim((string) $rangoFechas));
                    if ($lote === '24h') {
                        $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                    } elseif ($lote === 'semana') {
                        $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    } elseif ($lote === 'mes') {
                        $sql .= " AND fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    }
                }
            }

            $sql .= " ORDER BY nombre_completo ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::obtenerFiltrados Error] %s', $e->getMessage()));
            return [];
        }
    }

    /**
     * Actualiza la información de un funcionario registrado.
     *
     * @param string $cedulaOriginal Cédula actual del funcionario en BD.
     * @param string $nombre Nombre completo.
     * @param string $cargo Cargo institucional.
     * @param string $departamento Departamento o unidad.
     * @param string|null $nuevaCedula Nueva cédula si se modificó.
     * @return array{exito: bool, error?: string}
     */
    public function actualizar(
        string $cedulaOriginal,
        string $nombre,
        string $cargo,
        string $departamento,
        ?string $nuevaCedula = null
    ): array {
        $cedulaOriginal = trim($cedulaOriginal);
        $cedulaDestino  = !empty($nuevaCedula) ? trim($nuevaCedula) : $cedulaOriginal;

        // Si se cambia el número de cédula, validar que no colisione con otro funcionario
        if ($cedulaDestino !== $cedulaOriginal) {
            $existente = $this->buscarPorCedula($cedulaDestino);
            if ($existente) {
                return ['exito' => false, 'error' => 'Ya existe otro funcionario registrado con la cédula ' . $cedulaDestino . '.'];
            }
        }

        $sql = "UPDATE funcionarios_registrados 
                SET cedula = :nueva_cedula,
                    nombre_completo = :nombre,
                    cargo = :cargo,
                    departamento = :departamento
                WHERE cedula = :cedula_original";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nueva_cedula'    => $cedulaDestino,
                ':nombre'          => trim($nombre),
                ':cargo'           => trim($cargo),
                ':departamento'    => trim($departamento),
                ':cedula_original' => $cedulaOriginal
            ]);

            return ['exito' => true];
        } catch (Throwable $e) {
            error_log(sprintf('[FuncionarioModelo::actualizar Error] %s', $e->getMessage()));
            return ['exito' => false, 'error' => 'Error al actualizar el funcionario en la base de datos.'];
        }
    }
}

// Compatibilidad con namespace alternativo en minúsculas si se invoca con App\modelos
if (!class_exists('App\modelos\FuncionarioModelo', false)) {
    class_alias(FuncionarioModelo::class, 'App\modelos\FuncionarioModelo');
}
