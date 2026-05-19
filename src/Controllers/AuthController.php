<?php
declare(strict_types=1);

namespace Auth\Controllers;

use Exception;
use Auth\Database;
use PDO;
use Hybridauth\Hybridauth;

// Autoloader manual para as classes internas e para o Hybridauth
spl_autoload_register(function ($class) {
    if (strpos($class, 'Hybridauth\\') === 0) {
        $relativeClass = substr($class, 11);
        $file = __DIR__ . '/../libs/hybridauth/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) require_once $file;
    } elseif (strpos($class, 'Auth\\') === 0) {
        $relativeClass = substr($class, 5);
        $file = __DIR__ . '/../' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) require_once $file;
    }
});

class AuthController {
    
    public static function redirect(): void {
        // Inicializa a sessão para o Hybridauth conseguir lembrar dos estados entre os redirecionamentos
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Tenta pegar o provedor da URL (útil no clique inicial do botão)
        $provider = $_GET['provider'] ?? '';

        // 1. Carrega as configurações centrais do app
        $appConfig = require __DIR__ . '/../../config/app.php';

        // 2. Monta a estrutura que o Hybridauth precisa, puxando os dados limpos
        $config = [
            'callback'  => $appConfig['callback'],
            'providers' => $appConfig['providers']
        ];

        try {
            $hybridauth = new Hybridauth($config);

            // 1. Se o parâmetro veio na URL (momento do clique no botão), guardamos na sessão
            if (!empty($provider)) {
                $_SESSION['provider_ativo'] = $provider;
            } 
            // 2. Se a URL veio vazia (momento do retorno da rede social), resgatamos da sessão
            else {
                $provider = $_SESSION['provider_ativo'] ?? '';
            }

            // Se mesmo assim continuar vazio, é um acesso direto inválido
            if (empty($provider)) {
                die("Erro: Nenhum provedor de autenticação ativo foi detectado no retorno.");
            }

            // Conecta ou processa o retorno do provedor correto
            $adapter = $hybridauth->authenticate($provider);

            if ($adapter->isConnected()) {
                // Captura os dados do perfil do usuário
                $userProfile = $adapter->getUserProfile();

                // GUARDE O TOKEN AQUI: Acessa o token de acesso gerado para o Google
                $accessToken = $adapter->getAccessToken();
                $_SESSION['access_token'] = $accessToken['access_token'] ?? null;

                $db = Database::getConnection();
                
                // 1. Verifica se o usuário já existe no banco
                $stmt = $db->prepare("SELECT * FROM users WHERE social_id = ? AND provider = ?");
                $stmt->execute([$userProfile->identifier, $provider]);
                $usuarioBanco = $stmt->fetch();

                // Se o usuário JÁ existe e já tem um avatar personalizado salvo, usamos o que está no banco
                if ($usuarioBanco && !empty($usuarioBanco['avatar']) && $usuarioBanco['avatar'] !== 'assets/avatars/default.png') {
                    $avatarLocal = $usuarioBanco['avatar'];
                } else {
                    $avatarLocal = 'assets/avatars/default.png'; // Fallback padrão

                    // 2. Se tiver foto na rede social, faz o download usando Hash para o nome
                    if (!empty($userProfile->photoURL)) {
                        $diretorioAvatars = __DIR__ . '/../../public/assets/avatars/';
                        
                        if (!is_dir($diretorioAvatars)) {
                            mkdir($diretorioAvatars, 0755, true);
                        }

                        // Gera um nome único baseado no hash do ID social + timestamp
                        $extensao = 'jpg'; 
                        $nomeArquivo = md5($userProfile->identifier . time()) . '.' . $extensao;
                        $caminhoCompleto = $diretorioAvatars . $nomeArquivo;

                        // Baixa a imagem de forma segura
                        $context = stream_context_create(["http" => ["header" => "User-Agent: PHP\r\n"]]);
                        $conteudoFoto = @file_get_contents($userProfile->photoURL, false, $context);
                        
                        if ($conteudoFoto !== false) {
                            file_put_contents($caminhoCompleto, $conteudoFoto);
                            $avatarLocal = 'assets/avatars/' . $nomeArquivo;
                        }
                    }
                }

                if (!$usuarioBanco) {
                    // 3. Se não existe, faz o INSERT
                    $stmtInsert = $db->prepare("INSERT INTO users (social_id, provider, name, email, avatar) VALUES (?, ?, ?, ?, ?)");
                    $stmtInsert->execute([
                        $userProfile->identifier,
                        $provider,
                        $userProfile->displayName ?? 'Usuário',
                        $userProfile->email ?? 'nao-informado@teste.com',
                        $avatarLocal
                    ]);
                    
                    $idInserido = $db->lastInsertId();
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$idInserido]);
                    $usuarioBanco = $stmt->fetch();
                } else {
                    // 4. Se já existe e a foto antiga não for a padrão, podemos opcionalmente apagar a antiga se quiser,
                    // mas por garantia apenas atualizamos o caminho se uma nova foi baixada com sucesso
                    if ($avatarLocal !== 'assets/avatars/default.png') {
                        $stmtUpdate = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                        $stmtUpdate->execute([$avatarLocal, $usuarioBanco['id']]);
                        $usuarioBanco['avatar'] = $avatarLocal;
                    }
                }

                // Salva o modelo final do banco de dados na sessão para o Dashboard ler
                $_SESSION['usuario_logado'] = $usuarioBanco;

                // Limpa a variável temporária do provedor ativo
                unset($_SESSION['provider_ativo']);

                // Desconecta o adapter para limpar os tokens temporários da biblioteca
                $adapter->disconnect();

                // Fecha o popup injetando o script JS para a tela principal assumir o controle
                echo "<script>window.close();</script>";
                exit;
            }

        } catch (Exception $e) {
            echo "Erro no processo do " . htmlspecialchars($provider) . ": " . $e->getMessage();
            exit;
        }
    }
}