<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Funciones de seguridad transversales, usadas por OE4 (mitigaciones OWASP).
 *
 * Cubre:
 *  - A05 (Configuración de seguridad incorrecta) -> cabeceras HTTP
 *  - CSRF -> token por sesión
 *  - XSS -> función de saneamiento de salida (e())
 */
final class Security
{
    /**
     * Cabeceras de seguridad HTTP recomendadas por OWASP Secure Headers
     * Project. Deben enviarse en CADA respuesta, antes de cualquier salida.
     */
    public static function setSecurityHeaders(): void
    {
        // Evita que el navegador "adivine" el Content-Type (mitiga ataques
        // de MIME-sniffing).
        header('X-Content-Type-Options: nosniff');

        // Evita que la app se cargue dentro de un <iframe> de otro sitio
        // (mitiga clickjacking).
        header('X-Frame-Options: DENY');

        // Content-Security-Policy restrictiva: solo permite recursos del
        // propio origen. Ajustar 'self' si se agregan CDNs externos.
        header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'");

        // No envía el Referer completo a otros orígenes.
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Desactiva APIs del navegador que esta app no usa.
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Solo aplica si se sirve sobre HTTPS en producción (Strict Transport
        // Security). En local con Docker sobre HTTP no tiene efecto, pero se
        // documenta para el despliegue real.
        if (!empty($_SERVER['HTTPS'])) {
            header('Strict-Transport-Security: max-age=63072000; includeSubDomains');
        }
    }

    /**
     * Configura la cookie de sesión con flags seguras ANTES de iniciar la
     * sesión. Debe llamarse antes de session_start().
     *
     * HttpOnly  -> JavaScript no puede leer la cookie (mitiga robo por XSS)
     * Secure    -> la cookie solo viaja por HTTPS (en local se deja en false
     *              para poder probar sobre HTTP; en producción debe ser true)
     * SameSite  -> Strict mitiga CSRF al no enviar la cookie en peticiones
     *              cross-site
     */
    public static function configureSecureSession(): void
    {
        $secureFlag = !empty($_SERVER['HTTPS']);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secureFlag,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
    }

    /**
     * Genera (o reutiliza) el token CSRF de la sesión actual.
     * Se debe insertar como campo oculto en TODOS los formularios POST.
     */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token CSRF recibido contra el guardado en sesión.
     * Usa hash_equals() para comparación de tiempo constante (mitiga timing
     * attacks), igual que se hace con password_verify().
     */
    public static function validateCsrf(?string $tokenRecibido): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($tokenRecibido)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $tokenRecibido);
    }

    /**
     * Saneamiento de salida para prevenir XSS reflejado/almacenado.
     * Usar SIEMPRE al imprimir datos provenientes del usuario en HTML:
     *   <?= Security::e($mascota['nombre']) ?>
     *
     * ENT_QUOTES escapa comillas simples y dobles; UTF-8 evita bypasses por
     * codificación de caracteres.
     */
    public static function e(?string $valor): string
    {
        return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
    }
}
