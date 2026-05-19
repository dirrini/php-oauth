<?php
declare(strict_types=1);

session_start();

// Se o usuário já tiver uma sessão ativa, manda direto para o painel
if (isset($_SESSION['usuario_logado'])) {
    header('Location: /dashboard.php');
    exit;
}

// Certifique-se de que o autoload do composer está mapeando os arquivos
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as configurações dos provedores ativos
$config = require __DIR__ . '/../config/app.php';
$providers = $config['providers'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessar o Sistema</title>
    <!-- Tailwind CSS v4 via CDN Oficial -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-slate-100 text-center">
        <!-- Cadeado Superior -->
        <div class="mx-auto w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Bem-vindo ao Sistema!</h1>
        <p class="text-sm text-slate-500 mb-8">Escolha um dos provedores homologados abaixo para se autenticar.</p>

        <!-- Lista de Botões Acionados via JavaScript Popup -->
        <div class="flex flex-col gap-3">
            <?php foreach ($providers as $name => $data): ?>
                <?php if (!empty($data['enabled'])): ?>
                    
                    <button onclick="iniciarLoginSocial('<?php echo urlencode($name); ?>')" 
                            class="flex items-center justify-center gap-3 font-medium py-3 px-6 rounded-xl transition shadow-xs cursor-pointer text-sm w-full <?php echo $data['style']; ?>">
                        <?php echo $data['icon']; ?>
                        Entrar com o <?php echo $name; ?>
                    </button>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        
        <div class="mt-8 pt-4 border-t border-slate-100 text-xs text-slate-400">
            Estrutura Pronta • Ecossistema Hybridauth Ativo
        </div>
    </div>

    <!-- Script de Controle do Fluxo Assíncrono -->
    <script>
        function iniciarLoginSocial(provider) {
            // 1. Configura o tamanho e centraliza a janela popup na tela do usuário
            const largura = 600;
            const altura = 700;
            const esquerda = (screen.width / 2) - (largura / 2);
            const topo = (screen.height / 2) - (altura / 2);
            
            // 2. Abre a rota de callback passando uma flag indicando que é um popup
            const url = `/callback.php?provider=${provider}&mode=popup`;
            
            const popup = window.open(
                url, 
                'LoginSocial', 
                `width=${largura},height=${altura},top=${topo},left=${esquerda},scrollbars=yes,status=no`
            );
    
            // 3. Cria um temporizador para monitorar quando o usuário fechar a janela do Google/Facebook
            const monitorarPopup = setInterval(() => {
                if (popup.closed) {
                    clearInterval(monitorarPopup);
                    
                    // O popup fechou! Agora fazemos uma requisição de checagem em segundo plano via Fetch
                    verificarSessaoUsuario();
                }
            }, 500);
        }
    
        function verificarSessaoUsuario() {
            // Faz uma requisição assíncrona para o backend saber se o usuário está logado
            fetch('/api/login.php')
                .then(response => response.json())
                .then(data => {
                    if (data.logado) {
                        // Sucesso! Modifique o DOM de forma fluida sem recarregar a página
                        window.location.href = '/dashboard.php'; // Ou mude o estado da tela aqui
                    } else {
                        // do nothing!
                    }
                })
                .catch(error => console.error('Erro na requisição:', error));
        }
    </script>
</body>
</html>