<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Config\Database;
use App\Controllers\AuthController;
use App\Helpers\Security;
use App\Repositories\PdoUsuarioRepository;

// Si ya hay sesión activa, no tiene sentido ver el login de nuevo.
if (!empty($_SESSION['usuario_id'])) {
    header('Location: /index.php');
    exit;
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Validación CSRF: primera línea de defensa antes de tocar nada más ---
    if (!Security::validateCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $errores[] = 'Token de seguridad inválido o expirado. Recargue la página e intente de nuevo.';
    } else {
        $repositorio = new PdoUsuarioRepository(Database::getConnection());
        $authController = new AuthController($repositorio);

        $resultado = $authController->login(
            trim($_POST['email'] ?? ''),
            $_POST['password'] ?? ''
        );

        if ($resultado['success']) {
            header('Location: /index.php');
            exit;
        }

        $errores = $resultado['errors'];
    }
}

$csrfToken = Security::csrfToken();
require __DIR__ . '/../views/auth/login.php';
