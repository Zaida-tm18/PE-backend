<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Controllers\MascotaController;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Repositories\PdoMascotaRepository;

AuthMiddleware::requireAuth();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: /mascotas/index.php');
    exit;
}

$controller = new MascotaController(new PdoMascotaRepository(Database::getConnection()));
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        $errores[] = 'Token de seguridad inválido o expirado. Recargue la página e intente de nuevo.';
    } else {
        $resultado = $controller->actualizar($id, $_POST);

        if ($resultado['success']) {
            header('Location: /mascotas/index.php?ok=1');
            exit;
        }

        $errores = $resultado['errors'];
    }
}

$mascota = $controller->obtener($id);

if ($mascota === null) {
    header('Location: /mascotas/index.php');
    exit;
}

// Si el POST falló la validación, conservamos lo que el usuario escribió
// en vez de mostrar de nuevo los datos viejos de la BD.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($errores)) {
    $mascota = array_merge($mascota, $_POST);
}

$accion = '/mascotas/editar.php?id=' . $id;
$csrfToken = Security::csrfToken();

require __DIR__ . '/../../views/mascotas/formulario.php';