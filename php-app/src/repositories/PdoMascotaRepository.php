<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Implementación concreta del CRUD de Mascota con PDO + prepared statements.
 *
 * Requisito explícito de la guía (Paso 3): "ninguna query usa concatenación
 * de strings con datos del usuario". Por eso CADA valor variable entra por
 * un placeholder (:nombre, :especie, etc.), nunca interpolado en el SQL.
 */
final class PdoMascotaRepository implements MascotaRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listarTodas(): array
    {
        // Ordenamos por fecha de creación descendente: lo más reciente primero.
        $stmt = $this->pdo->query(
            'SELECT id, nombre, especie, raza, fecha_nacimiento, propietario_nombre,
                    propietario_telefono, usuario_id, creado_en
             FROM mascotas
             ORDER BY creado_en DESC'
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nombre, especie, raza, fecha_nacimiento, propietario_nombre,
                    propietario_telefono, usuario_id, creado_en
             FROM mascotas
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $mascota = $stmt->fetch(PDO::FETCH_ASSOC);

        return $mascota ?: null;
    }

    public function crear(array $datos): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO mascotas
                (nombre, especie, raza, fecha_nacimiento, propietario_nombre, propietario_telefono, usuario_id)
             VALUES
                (:nombre, :especie, :raza, :fecha_nacimiento, :propietario_nombre, :propietario_telefono, :usuario_id)
             RETURNING id'
        );

        $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':especie' => $datos['especie'],
            ':raza' => $datos['raza'] !== '' ? $datos['raza'] : null,
            ':fecha_nacimiento' => $datos['fecha_nacimiento'] !== '' ? $datos['fecha_nacimiento'] : null,
            ':propietario_nombre' => $datos['propietario_nombre'],
            ':propietario_telefono' => $datos['propietario_telefono'] !== '' ? $datos['propietario_telefono'] : null,
            ':usuario_id' => $datos['usuario_id'],
        ]);

        // En PostgreSQL, lastInsertId() requiere conocer el nombre de la
        // secuencia; usar RETURNING id es la forma portable y recomendada.
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE mascotas SET
                nombre = :nombre,
                especie = :especie,
                raza = :raza,
                fecha_nacimiento = :fecha_nacimiento,
                propietario_nombre = :propietario_nombre,
                propietario_telefono = :propietario_telefono
             WHERE id = :id'
        );

        return $stmt->execute([
            ':nombre' => $datos['nombre'],
            ':especie' => $datos['especie'],
            ':raza' => $datos['raza'] !== '' ? $datos['raza'] : null,
            ':fecha_nacimiento' => $datos['fecha_nacimiento'] !== '' ? $datos['fecha_nacimiento'] : null,
            ':propietario_nombre' => $datos['propietario_nombre'],
            ':propietario_telefono' => $datos['propietario_telefono'] !== '' ? $datos['propietario_telefono'] : null,
            ':id' => $id,
        ]);
    }

    public function eliminar(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM mascotas WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}