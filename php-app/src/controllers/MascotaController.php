<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MascotaRepositoryInterface;

/**
 * Controlador del CRUD de Mascota. Aquí vive la lógica de negocio
 * (validación, reglas) y delega el acceso a datos al repositorio.
 */
final class MascotaController
{
    /** Especies aceptadas, para no dejar el campo libre sin control. */
    private const ESPECIES_VALIDAS = ['Perro', 'Gato', 'Ave', 'Conejo', 'Reptil', 'Otro'];

    public function __construct(private MascotaRepositoryInterface $mascotas)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listar(): array
    {
        return $this->mascotas->listarTodas();
    }

    /** @return array<string, mixed>|null */
    public function obtener(int $id): ?array
    {
        return $this->mascotas->buscarPorId($id);
    }

    /**
     * @return array{success: bool, errors: string[]}
     */
    public function crear(array $datos, int $usuarioId): array
    {
        $errores = $this->validar($datos);

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $datos['usuario_id'] = $usuarioId;
        $this->mascotas->crear($datos);

        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array{success: bool, errors: string[]}
     */
    public function actualizar(int $id, array $datos): array
    {
        if (!$this->mascotas->buscarPorId($id)) {
            return ['success' => false, 'errors' => ['La mascota no existe.']];
        }

        $errores = $this->validar($datos);

        if (!empty($errores)) {
            return ['success' => false, 'errors' => $errores];
        }

        $this->mascotas->actualizar($id, $datos);

        return ['success' => true, 'errors' => []];
    }

    public function eliminar(int $id): bool
    {
        return $this->mascotas->eliminar($id);
    }

    /** @return string[] */
    private function validar(array $datos): array
    {
        $errores = [];

        $nombre = trim($datos['nombre'] ?? '');
        $especie = trim($datos['especie'] ?? '');
        $propietario = trim($datos['propietario_nombre'] ?? '');
        $fechaNacimiento = trim($datos['fecha_nacimiento'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre de la mascota es obligatorio (máx. 100 caracteres).';
        }

        if (!in_array($especie, self::ESPECIES_VALIDAS, true)) {
            $errores[] = 'Seleccione una especie válida.';
        }

        if ($propietario === '' || mb_strlen($propietario) > 150) {
            $errores[] = 'El nombre del propietario es obligatorio (máx. 150 caracteres).';
        }

        if ($fechaNacimiento !== '') {
            $fecha = \DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
            $valida = $fecha && $fecha->format('Y-m-d') === $fechaNacimiento;

            if (!$valida || $fecha > new \DateTime()) {
                $errores[] = 'La fecha de nacimiento no es válida o es futura.';
            }
        }

        return $errores;
    }
}