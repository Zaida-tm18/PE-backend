<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Contrato del repositorio de Usuario. Al programar contra esta interfaz
 * (y no contra PdoUsuarioRepository directamente), el AuthController no
 * sabe ni le importa si los datos vienen de MySQL, de un mock en memoria
 * para pruebas, o de otra fuente. Esto es lo que pide OE2/5.3.
 */
interface UsuarioRepositoryInterface
{
    public function buscarPorEmail(string $email): ?array;

    public function crear(string $nombre, string $email, string $passwordHash, string $rol): int;

    public function existeEmail(string $email): bool;
}
