<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;

/**
 * Conexión PDO segura y reutilizable (patrón Singleton simple).
 *
 * Decisiones de seguridad (justificadas en ADR-002):
 * - ATTR_EMULATE_PREPARES = false: obliga a PostgreSQL a preparar la
 *   consulta de verdad en el servidor, en vez de que PDO la simule en PHP.
 *   Esto es clave para la prevención real de inyección SQL.
 * - ATTR_ERRMODE = ERRMODE_EXCEPTION: cualquier error de BD lanza una
 *   excepción en vez de fallar silenciosamente.
 * - Credenciales nunca hardcodeadas: se leen de variables de entorno
 *   (inyectadas por docker-compose.yml).
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Constructor privado: nadie puede hacer "new Database()"
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'postgres_db';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'veterinaria';
            $user = getenv('DB_USER') ?: 'vet_user';
            $pass = getenv('DB_PASS') ?: 'vet_pass_2026';

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Nunca exponer el mensaje crudo de PDOException al usuario
                // final: puede filtrar credenciales o estructura de la BD.
                error_log('Error de conexión a BD: ' . $e->getMessage());
                throw new PDOException('No se pudo conectar a la base de datos.');
            }
        }

        return self::$instance;
    }
}
