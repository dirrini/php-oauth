<?php
declare(strict_types=1);

header('Content-Type: application/json');
session_start();

// Simulando uma checagem de sessão simples (substitua pela sua lógica de banco depois)
if (isset($_SESSION['usuario_logado'])) {
    echo json_encode([
        'logado' => true,
        'user' => $_SESSION['usuario_logado']
    ]);
} else {
    echo json_encode(['logado' => false]);
}