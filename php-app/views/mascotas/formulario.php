<?php
/**
 * Formulario reutilizable para Crear y Editar mascota.
 *
 * @var array<string, mixed>|null $mascota   null cuando es creación
 * @var string[] $errores
 * @var string $csrfToken
 * @var string $accion                       URL de destino del formulario
 */

use App\Helpers\Security;

$esEdicion = $mascota !== null;
$titulo = $esEdicion ? 'Editar mascota' : 'Registrar mascota';
$especies = ['Perro', 'Gato', 'Ave', 'Conejo', 'Reptil', 'Otro'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= Security::e($titulo) ?> - Sistema Veterinario</title>
</head>
<body>
    <h1><?= Security::e($titulo) ?></h1>

    <?php if (!empty($errores)): ?>
        <ul style="color:red;">
            <?php foreach ($errores as $error): ?>
                <li><?= Security::e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="<?= Security::e($accion) ?>">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrfToken) ?>">

        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= (int) $mascota['id'] ?>">
        <?php endif; ?>

        <label for="nombre">Nombre de la mascota</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100"
               value="<?= Security::e($mascota['nombre'] ?? '') ?>">

        <label for="especie">Especie</label>
        <select id="especie" name="especie" required>
            <option value="">-- Seleccione --</option>
            <?php foreach ($especies as $especie): ?>
                <option value="<?= Security::e($especie) ?>"
                    <?= (($mascota['especie'] ?? '') === $especie) ? 'selected' : '' ?>>
                    <?= Security::e($especie) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="raza">Raza (opcional)</label>
        <input type="text" id="raza" name="raza" maxlength="100"
               value="<?= Security::e($mascota['raza'] ?? '') ?>">

        <label for="fecha_nacimiento">Fecha de nacimiento (opcional)</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
               value="<?= Security::e($mascota['fecha_nacimiento'] ?? '') ?>">

        <label for="propietario_nombre">Nombre del propietario</label>
        <input type="text" id="propietario_nombre" name="propietario_nombre" required maxlength="150"
               value="<?= Security::e($mascota['propietario_nombre'] ?? '') ?>">

        <label for="propietario_telefono">Teléfono del propietario (opcional)</label>
        <input type="text" id="propietario_telefono" name="propietario_telefono" maxlength="20"
               value="<?= Security::e($mascota['propietario_telefono'] ?? '') ?>">

        <button type="submit"><?= $esEdicion ? 'Guardar cambios' : 'Registrar' ?></button>
    </form>

    <p><a href="/mascotas/index.php">&laquo; Volver al listado</a></p>
</body>
</html>
