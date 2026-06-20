<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Controllers\MascotaController;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Repositories\PdoMascotaRepository;

AuthMiddleware::requireAuth();

// Eliminar SOLO se acepta por POST (nunca por GET/link directo), y siempre
// con token CSRF válido: evita que un <img src="/mascotas/eliminar.php?id=1">
// malicioso en otra página borre datos sin que el usuario lo sepa.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo 'Solicitud inválida.';
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id > 0) {
    $controller = new MascotaController(new PdoMascotaRepository(Database::getConnection()));
    $controller->eliminar($id);
}

header('Location: /mascotas/index.php?ok=1');
exit;