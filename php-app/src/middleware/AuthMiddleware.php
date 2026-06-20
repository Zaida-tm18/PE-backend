<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Protección de rutas (OE1). Cualquier controlador que requiera sesión
 * activa llama a AuthMiddleware::requireAuth() como primera línea.
 *
 * Mitiga A01 (Control de Acceso Roto): nadie puede ver/editar/borrar
 * mascotas sin haber iniciado sesión, sin importar si conoce la URL directa.
 */
final class AuthMiddleware
{
    public static function requireAuth(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: /login.php?error=sesion_requerida');
            exit;
        }
    }

    /**
     * Variante para proteger acciones que solo el rol admin debería poder
     * ejecutar (ejemplo de control de acceso a nivel de rol, ampliable).
     */
    public static function requireRole(string $rolRequerido): void
    {
        self::requireAuth();

        if (($_SESSION['rol'] ?? null) !== $rolRequerido) {
            http_response_code(403);
            echo 'No tiene permisos para acceder a este recurso.';
            exit;
        }
    }
}
