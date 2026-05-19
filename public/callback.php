<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';

use Auth\Controllers\AuthController;

// Dispara o fluxo integrado
AuthController::redirect();