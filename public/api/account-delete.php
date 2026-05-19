<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/Database.php';
use Auth\Database;

session_start();

if (isset($_SESSION['usuario_logado'])) {
    $user = $_SESSION['usuario_logado'];
    $db = Database::getConnection();

    $provider = strtolower($user['provider']);
    if (!empty($_SESSION['access_token'])) {
        $token = $_SESSION['access_token'];

        if ($provider === 'google') {
            $urlRevoga = "https://oauth2.googleapis.com/revoke?token=" . urlencode($token);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlRevoga);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
            
        } elseif ($provider === 'facebook') {
            $urlRevoga = "https://graph.facebook.com/v20.0/me/permissions?access_token=" . urlencode($token);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlRevoga);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
        }

    }

    // 2. Apaga o arquivo físico da foto de dentro de public/assets/avatars/
    if (!empty($user['avatar']) && $user['avatar'] !== 'assets/avatars/default.png') {
        $caminhoFisico = __DIR__ . '/../' . $user['avatar'];
        if (file_exists($caminhoFisico)) {
            @unlink($caminhoFisico);
        }
    }

    // 3. Remove a linha correspondente do banco de dados
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);

    // 4. Limpa as variáveis e destrói a sessão de login
    $_SESSION = [];
    session_destroy();
}

// Redireciona o usuário limpo de volta para a Home
header('Location: /');
exit;