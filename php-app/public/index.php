<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Helpers\Security;
use App\Middleware\AuthMiddleware;

// Ruta protegida: si no hay sesión activa, redirige a /login.php
AuthMiddleware::requireAuth();

$nombreUsuario = Security::e($_SESSION['nombre'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Sistema Veterinario</title>
</head>
<body>
    <h1>Bienvenido(a), <?= $nombreUsuario ?></h1>
    <p>Rol: <?= Security::e($_SESSION['rol'] ?? '') ?></p>

    <nav>
        <a href="/mascotas/index.php">Gestionar mascotas</a> |
        <a href="/logout.php">Cerrar sesión</a>
    </nav>
</body>
</html>
