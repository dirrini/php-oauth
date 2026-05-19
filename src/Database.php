<?php
declare(strict_types=1);

namespace Auth;

use PDO;
use Exception;

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Carrega o arquivo de configuração centralizado
            $config = require __DIR__ . '/../config/app.php';
            $dbConfig = $config['db'];

            try {
                self::$instance = new PDO(
                    "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
                    $dbConfig['user'],
                    $dbConfig['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (Exception $e) {
                die("Erro ao conectar no banco de dados: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}