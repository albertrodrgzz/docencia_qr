<?php
declare(strict_types=1);

namespace App\Modelos;

use Configuracion\ConexionBD;
use PDO;
use PDOException;
use Throwable;

/**
 * Clase UsuarioModelo
 * 
 * Gestiona el acceso y autenticación de usuarios del sistema en la base de datos.
 */
class UsuarioModelo
{
    private PDO $pdo;

    /**
     * Constructor del modelo.
     * 
     * @param PDO|null $pdo Instancia de PDO. Si es null, utiliza ConexionBD::getInstancia().
     */
    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? ConexionBD::getInstancia();
    }

    /**
     * Autentica un usuario verificando correo, estado activo y hash bcrypt.
     *
     * @param string $correo Correo electrónico del usuario.
     * @param string $password Contraseña en texto plano.
     * @return array<string, mixed>|false Datos del usuario autenticado (sin password_hash) o false si falla.
     */
    public function autenticar(string $correo, string $password): array|false
    {
        $sql = "SELECT id_usuario, correo, password_hash, rol, estado 
                FROM usuarios_sistema 
                WHERE correo = :correo 
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':correo' => trim($correo)]);
            $usuario = $stmt->fetch();

            // Si el correo no existe en la base de datos
            if (!$usuario) {
                return false;
            }

            // Validar que el usuario esté en estado 'Activo'
            if ($usuario['estado'] !== 'Activo') {
                return false;
            }

            // Comprobar la contraseña mediante Bcrypt seguro
            if (!password_verify($password, $usuario['password_hash'])) {
                return false;
            }

            // Retornar información del usuario omitiendo el hash de la contraseña por seguridad
            return [
                'id'         => (int) $usuario['id_usuario'],
                'id_usuario' => (int) $usuario['id_usuario'],
                'correo'     => (string) $usuario['correo'],
                'rol'        => (string) $usuario['rol'],
                'estado'     => (string) $usuario['estado']
            ];
        } catch (PDOException $e) {
            error_log(sprintf('[UsuarioModelo::autenticar Error PDO] %s', $e->getMessage()));
            return false;
        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioModelo::autenticar Error General] %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Lista todos los usuarios registrados en el sistema institucional.
     *
     * @return array<int, array<string, mixed>> Arreglo asociativo con la lista de usuarios.
     */
    public function listarUsuarios(): array
    {
        $sql = "SELECT id_usuario, nombre, correo, rol, estado, fecha_registro 
                FROM usuarios_sistema 
                ORDER BY id_usuario DESC";

        try {
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioModelo::listarUsuarios Error] %s', $e->getMessage()));
            return [];
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos con rol asignado ('admin' o 'estandar').
     *
     * @param string $nombre Nombre completo del usuario.
     * @param string $correo Correo electrónico institucional.
     * @param string $password_hash Contraseña cifrada con password_hash().
     * @param string $rol Rol asignado ('admin' o 'estandar').
     * @return array{exito: bool, id_usuario?: int, error?: string}
     */
    public function crearUsuario(string $nombre, string $correo, string $password_hash, string $rol): array
    {
        // Normalizar rol: asegurar 'admin' o 'estandar'
        $rolLimpio = match (strtolower(trim($rol))) {
            'admin', 'administrador' => 'admin',
            default                  => 'estandar',
        };

        $sql = "INSERT INTO usuarios_sistema (nombre, correo, password_hash, rol, estado, fecha_registro) 
                VALUES (:nombre, :correo, :password_hash, :rol, 'Activo', NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'        => trim($nombre),
                ':correo'        => trim($correo),
                ':password_hash' => $password_hash,
                ':rol'           => $rolLimpio,
            ]);

            return [
                'exito'      => true,
                'id_usuario' => (int) $this->pdo->lastInsertId(),
            ];
        } catch (PDOException $e) {
            // Error de duplicidad de correo (código SQLSTATE 23000 / 1062)
            if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? 0) === 1062) {
                return [
                    'exito' => false,
                    'error' => 'El correo electrónico ya se encuentra registrado en el sistema.',
                ];
            }

            error_log(sprintf('[UsuarioModelo::crearUsuario Error PDO] %s', $e->getMessage()));
            return [
                'exito' => false,
                'error' => 'Error al guardar el usuario en la base de datos.',
            ];
        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioModelo::crearUsuario Error General] %s', $e->getMessage()));
            return [
                'exito' => false,
                'error' => 'Ocurrió un error inesperado al crear el usuario.',
            ];
        }
    }

    /**
     * Busca un usuario por su identificador primario.
     *
     * @param int $id ID de usuario.
     * @return array<string, mixed>|false
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT id_usuario, nombre, correo, rol, estado, fecha_registro 
                FROM usuarios_sistema 
                WHERE id_usuario = :id 
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioModelo::buscarPorId Error] %s', $e->getMessage()));
            return false;
        }
    }

    /**
     * Actualiza la información y/o credenciales de un usuario existente.
     *
     * @param int $id ID del usuario a modificar.
     * @param string $nombre Nombre completo.
     * @param string $correo Correo institucional.
     * @param string $rol Rol ('admin' o 'estandar').
     * @param string|null $password Contraseña en texto plano si se desea cambiar.
     * @param string $estado 'Activo' o 'Inactivo'.
     * @return array{exito: bool, error?: string}
     */
    public function actualizarUsuario(
        int $id,
        string $nombre,
        string $correo,
        string $rol,
        ?string $password = null,
        string $estado = 'Activo'
    ): array {
        $rolLimpio = match (strtolower(trim($rol))) {
            'admin', 'administrador' => 'admin',
            default                  => 'estandar',
        };
        $estadoLimpio = (trim($estado) === 'Inactivo') ? 'Inactivo' : 'Activo';

        // Validar unicidad del correo electrónico
        $sqlCheck = "SELECT id_usuario FROM usuarios_sistema WHERE correo = :correo AND id_usuario != :id LIMIT 1";
        try {
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':correo' => trim($correo), ':id' => $id]);
            if ($stmtCheck->fetch()) {
                return ['exito' => false, 'error' => 'El correo electrónico ya se encuentra registrado por otro usuario.'];
            }

            if (!empty($password) && strlen(trim($password)) >= 6) {
                $sql = "UPDATE usuarios_sistema 
                        SET nombre = :nombre, 
                            correo = :correo, 
                            rol = :rol, 
                            estado = :estado, 
                            password_hash = :hash 
                        WHERE id_usuario = :id";
                $params = [
                    ':nombre' => trim($nombre),
                    ':correo' => trim($correo),
                    ':rol'    => $rolLimpio,
                    ':estado' => $estadoLimpio,
                    ':hash'   => password_hash(trim($password), PASSWORD_BCRYPT),
                    ':id'     => $id
                ];
            } else {
                $sql = "UPDATE usuarios_sistema 
                        SET nombre = :nombre, 
                            correo = :correo, 
                            rol = :rol, 
                            estado = :estado 
                        WHERE id_usuario = :id";
                $params = [
                    ':nombre' => trim($nombre),
                    ':correo' => trim($correo),
                    ':rol'    => $rolLimpio,
                    ':estado' => $estadoLimpio,
                    ':id'     => $id
                ];
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return ['exito' => true];
        } catch (Throwable $e) {
            error_log(sprintf('[UsuarioModelo::actualizarUsuario Error] %s', $e->getMessage()));
            return ['exito' => false, 'error' => 'Error al actualizar el usuario en la base de datos.'];
        }
    }
}

// Compatibilidad con namespace en minúsculas si se invoca con App\modelos
if (!class_exists('App\modelos\UsuarioModelo', false)) {
    class_alias(UsuarioModelo::class, 'App\modelos\UsuarioModelo');
}
