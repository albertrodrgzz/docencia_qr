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
}

// Compatibilidad con namespace alternativo en minúsculas si se invoca con App\modelos
if (!class_exists('App\modelos\FuncionarioModelo', false)) {
    class_alias(FuncionarioModelo::class, 'App\modelos\FuncionarioModelo');
}
