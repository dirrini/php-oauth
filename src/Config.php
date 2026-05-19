<?php
declare(strict_types=1);

namespace Auth;

class Config {
    public static function getHybridauthConfig(): array {
        $appConfig = require __DIR__ . '/../config/app.php';
        return $appConfig;
    }
}