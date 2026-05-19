<?php
declare(strict_types=1);

session_start();

// Verifica se existe uma sessão ativa antes de destruir
if (isset($_SESSION['usuario_logado'])) {
    // 1. Limpa todas as variáveis de sessão
    $_SESSION = [];

    // 2. Se houver cookies de sessão ativos no navegador, invalida-os
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }

    // 3. Destrói a sessão no servidor
    session_destroy();
}

// 4. Redireciona de forma limpa para a tela inicial
header('Location: /');
exit;