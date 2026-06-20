<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Contrato del repositorio de Mascota (entidad del CRUD, OE2).
 *
 * Las 5 operaciones que pide el Paso 3 de la guía: Crear, Listar (todas),
 * Obtener (una), Actualizar, Eliminar.
 */
interface MascotaRepositoryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function listarTodas(): array;

    /** @return array<string, mixed>|null */
    public function buscarPorId(int $id): ?array;

    public function crear(array $datos): int;

    public function actualizar(int $id, array $datos): bool;

    public function eliminar(int $id): bool;
}