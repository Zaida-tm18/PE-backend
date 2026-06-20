<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Controllers\AuthController;
use App\Repositories\PdoUsuarioRepository;
use App\Config\Database;

// El logout no necesita el repositorio, pero mantenemos la misma forma de
// construir el controlador por consistencia con login/registro.
$authController = new AuthController(new PdoUsuarioRepository(Database::getConnection()));
$authController->logout();

header('Location: /login.php');
exit;
