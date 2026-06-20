<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Security;
use App\Repositories\UsuarioRepositoryInterface;
use PDOException;

/**
 * Controlador de autenticación (OE1).
 *
 * Cubre: registro con validación server-side, login con password_verify(),
 * regeneración de ID de sesión, y logout que destruye la sesión por
 * completo.
 */
final class AuthController
{
    public function __construct(private UsuarioRepositoryInterface $usuarios)
    {
    }

    /**
     * @return array{success: bool, errors: string[]}
     */
    public function registrar(array $datos): array
    {
        $errores = [];

        $nombre = trim($datos['nombre'] ?? '');
        $email = trim($datos['email'] ?? '');
        $password = $datos['password'] ?? '';
        $passwordConfirm = $datos['password_confirm'] ?? '';

        // --- Validación server-side (nunca confiar solo en validación JS) ---
        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errores[] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }

        // Política de contraseña: mínimo 8 caracteres, al menos una letra y
        // un número. Esto mitiga parcialmente A07 (Fallas de Autenticación).
        if (
            mb_strlen($password) < 8
            || !preg_match('/[A-Za-z]/', $password)
            || !preg_match('/[0-9]/', $password)
        ) {
            $errores[] = 'La contraseña debe tener mínimo 8 caracteres, con letras y números.';
        }

        if ($password !== $passwordConfirm) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errores) && $this->usuarios->existeEmail($email)) {
            // Mensaje genérico (no decir "el email ya existe" de forma
            // distinta a otros errores) para no facilitar enumeración de
            // cuentas, pero aquí lo hacemos explícito por claridad didáctica.
            $errores[] = 'Ese correo ya está registrado.';
        }

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        try {
            // password_hash con PASSWORD_ARGON2ID si la extensión está
            // disponible; PHP cae a BCRYPT automáticamente si no lo está.
            $algoritmo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
            $hash = password_hash($password, $algoritmo);

            $this->usuarios->crear($nombre, $email, $hash, 'recepcionista');

            return ['success' => true, 'errors' => []];
        } catch (PDOException $e) {
            error_log('Error al registrar usuario: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['Ocurrió un error al registrar. Intente nuevamente.']];
        }
    }

    /**
     * @return array{success: bool, errors: string[]}
     */
    public function login(string $email, string $password): array
    {
        $usuario = $this->usuarios->buscarPorEmail($email);

        // OJO: aunque el usuario no exista, igual llamamos a un
        // password_verify "dummy" para que el tiempo de respuesta sea
        // similar al caso en que sí existe. Esto mitiga timing attacks que
        // permitirían enumerar emails válidos por la diferencia de tiempo.
        $hashParaVerificar = $usuario['password_hash']
            ?? '$argon2id$v=19$m=65536,t=4,p=1$ZHVtbXlzYWx0ZHVtbXk$ZHVtbXlkdW1teWR1bW15ZHVtbXlkdW1teQ';

        $passwordValido = password_verify($password, $hashParaVerificar);

        if (!$usuario || !$passwordValido) {
            return ['success' => false, 'errors' => ['Credenciales inválidas.']];
        }

        // --- Regeneración de ID de sesión al autenticarse ---
        // Mitiga session fixation: un atacante que haya fijado un ID de
        // sesión antes del login no puede reusarlo después.
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['login_time'] = time();

        return ['success' => true, 'errors' => []];
    }

    public function logout(): void
    {
        $_SESSION = [];

        // Elimina también la cookie de sesión del navegador, no solo los
        // datos del lado del servidor.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}
