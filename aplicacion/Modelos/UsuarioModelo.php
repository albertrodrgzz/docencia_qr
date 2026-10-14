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
}

// Compatibilidad con namespace en minúsculas si se invoca con App\modelos
if (!class_exists('App\modelos\UsuarioModelo', false)) {
    class_alias(UsuarioModelo::class, 'App\modelos\UsuarioModelo');
}
