<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Implementación concreta del repositorio de Usuario usando PDO con
 * prepared statements. NINGUNA consulta aquí concatena datos del usuario
 * directamente en el string SQL (requisito de OE2).
 */
final class PdoUsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email LIMIT 1'
        );
        // bindValue: el valor se copia en el momento del bind (a diferencia
        // de bindParam, que liga por referencia y solo evalúa la variable
        // al ejecutar). Para un valor fijo como este, bindValue es más claro.
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }

    public function crear(string $nombre, string $email, string $passwordHash, string $rol): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (:nombre, :email, :password_hash, :rol) RETURNING id'
        );
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':rol' => $rol,
        ]);

        // En PostgreSQL, lastInsertId() requiere conocer el nombre de la
        // secuencia; usar RETURNING id es la forma portable y recomendada.
        return (int) $stmt->fetchColumn();
    }

    public function existeEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }
}
