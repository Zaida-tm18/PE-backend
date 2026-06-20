<?php
/** @var array<int, array<string, mixed>> $mascotas */

use App\Helpers\Security;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mascotas - Sistema Veterinario</title>
</head>
<body>
    <h1>Mascotas registradas</h1>

    <p>
        <a href="/index.php">&laquo; Volver al panel</a> |
        <a href="/mascotas/crear.php">+ Registrar nueva mascota</a>
    </p>

    <?php if (!empty($_GET['ok'])): ?>
        <p style="color:green;">Operación realizada con éxito.</p>
    <?php endif; ?>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
        <tr>
            <th>Nombre</th>
            <th>Especie</th>
            <th>Raza</th>
            <th>Propietario</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($mascotas)): ?>
            <tr><td colspan="6">No hay mascotas registradas todavía.</td></tr>
        <?php endif; ?>

        <?php foreach ($mascotas as $mascota): ?>
            <tr>
                <!-- Security::e() escapa cada valor antes de imprimirlo: -->
                <!-- mitigación de XSS almacenado (un nombre malicioso guardado -->
                <!-- en la BD no se ejecuta como HTML/JS al renderizarse). -->
                <td><?= Security::e($mascota['nombre']) ?></td>
                <td><?= Security::e($mascota['especie']) ?></td>
                <td><?= Security::e($mascota['raza'] ?? '-') ?></td>
                <td><?= Security::e($mascota['propietario_nombre']) ?></td>
                <td><?= Security::e($mascota['propietario_telefono'] ?? '-') ?></td>
                <td>
                    <a href="/mascotas/editar.php?id=<?= (int) $mascota['id'] ?>">Editar</a>
                    &nbsp;|&nbsp;
                    <form method="POST" action="/mascotas/eliminar.php" style="display:inline;"
                          onsubmit="return confirm('¿Eliminar esta mascota?');">
                        <input type="hidden" name="csrf_token" value="<?= Security::e($csrfToken) ?>">
                        <input type="hidden" name="id" value="<?= (int) $mascota['id'] ?>">
                        <button type="submit">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
