<?php
/** @var string $csrfToken */
/** @var string[] $errores */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Veterinaria</title>
</head>
<body>
    <h1>Crear cuenta</h1>

    <?php if (!empty($errores)): ?>
        <ul style="color:red;">
            <?php foreach ($errores as $error): ?>
                <li><?= \App\Helpers\Security::e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="/register.php">
        <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::e($csrfToken) ?>">

        <label for="nombre">Nombre completo</label>
        <input type="text" id="nombre" name="nombre" required minlength="3">

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required minlength="8">

        <label for="password_confirm">Confirmar contraseña</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

        <button type="submit">Registrarse</button>
    </form>

    <p>¿Ya tiene cuenta? <a href="/login.php">Inicie sesión</a></p>
</body>
</html>
