<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Controllers\MascotaController;
use App\Helpers\Security;
use App\Middleware\AuthMiddleware;
use App\Repositories\PdoMascotaRepository;

// Ruta protegida (OE1): sin sesión activa no se puede ver el listado.
AuthMiddleware::requireAuth();

$controller = new MascotaController(new PdoMascotaRepository(Database::getConnection()));
$mascotas = $controller->listar();
$csrfToken = Security::csrfToken();

require __DIR__ . '/../../views/mascotas/index.php';
