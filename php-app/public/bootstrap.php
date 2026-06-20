<?php

declare(strict_types=1);

use App\Helpers\Security;

require_once __DIR__ . '/../vendor/autoload.php';

// 1. Cabeceras de seguridad en TODA respuesta, antes de cualquier salida.
Security::setSecurityHeaders();

// 2. Cookie de sesión con flags seguras + inicio de sesión.
//    Debe ir antes de cualquier acceso a $_SESSION.
Security::configureSecureSession();