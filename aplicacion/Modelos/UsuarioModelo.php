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
}

// Compatibilidad con namespace en minúsculas si se invoca con App\modelos
if (!class_exists('App\modelos\UsuarioModelo', false)) {
    class_alias(UsuarioModelo::class, 'App\modelos\UsuarioModelo');
}
