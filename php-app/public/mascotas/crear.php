<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Controllers\MascotaController;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Repositories\PdoMascotaRepository;

AuthMiddleware::requireAuth();

$controller = new MascotaController(new PdoMascotaRepository(Database::getConnection()));
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $errores[] = 'Token de seguridad inválido o expirado. Recargue la página e intente de nuevo.';
    } else {
        $resultado = $controller->crear($_POST, (int) $_SESSION['usuario_id']);

        if ($resultado['success']) {
            header('Location: /mascotas/index.php?ok=1');
            exit;
        }

        $errores = $resultado['errors'];
    }
}

$mascota = null; // null => el formulario sabe que es modo "crear"
$accion = '/mascotas/crear.php';
$csrfToken = Security::csrfToken();

require __DIR__ . '/../../views/mascotas/formulario.php';