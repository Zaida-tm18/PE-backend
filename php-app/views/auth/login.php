<?php
/** @var string $csrfToken */
/** @var string[] $errores */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Veterinaria</title>
</head>
<body>
    <h1>Iniciar sesión</h1>

    <?php if (!empty($errores)): ?>
        <ul style="color:red;">
            <?php foreach ($errores as $error): ?>
                <li><?= \App\Helpers\Security::e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="/login.php">
        <!-- Token CSRF oculto: el servidor lo valida antes de procesar el POST -->
        <input type="hidden" name="csrf_token" value="<?= \App\Helpers\Security::e($csrfToken) ?>">

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Ingresar</button>
    </form>

    <p>¿No tiene cuenta? <a href="/register.php">Regístrese aquí</a></p>
</body>
</html>
