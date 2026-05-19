<?php
declare(strict_types=1);

session_start();

// Se não estiver logado, chuta de volta para a index
if (!isset($_SESSION['usuario_logado'])) {
    header('Location: /index.php');
    exit;
}

$user = $_SESSION['usuario_logado'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Usuário</title>
    <!-- Tailwind CSS v4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- ADICIONE ESTA LINHA (Player Oficial para arquivos .lottie): -->
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border border-slate-100 text-center relative overflow-hidden">
        
        <!-- Faixa decorativa superior baseada na rede social -->
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

        <div class="mx-auto w-40 h-40 flex justify-center items-center">
            <dotlottie-player 
                src="/assets/success-celebration.lottie" 
                background="transparent" 
                speed="1" 
                style="width: 240px; height: 240px;" 
                loop 
                autoplay>
            </dotlottie-player>
        </div>

        <!-- Exibição do Avatar Redondo -->
        <div class="relative inline-block mb-4">
            <img src="/<?php echo htmlspecialchars($user['avatar']); ?>" 
                 alt="Avatar" 
                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md mx-auto">
            <span class="absolute bottom-0 right-1 bg-green-500 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white" title="Conectado via <?php echo $user['provider']; ?>">
                ✓
            </span>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($user['name']); ?></h1>
        <p class="text-sm text-slate-400 mb-4"><?php echo htmlspecialchars($user['email']); ?></p>
        
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 mb-8">
            Provedor: <span class="capitalize font-bold"><?php echo htmlspecialchars($user['provider']); ?></span>
        </div>

        <!-- Grade de Ações -->
        <div class="flex flex-col gap-3">
            <!-- Link Voltar à Inicial (Agora Deslogando) -->
            <a href="/api/logout.php" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-3 px-6 rounded-xl transition text-sm flex items-center justify-center gap-2">
                ← Voltar para a Home
            </a>

            <!-- Botão Excluir e Deslogar -->
            <a href="/api/account-delete.php" 
               onclick="return confirm('Tem certeza absoluta que deseja excluir seus dados e encerrar sua conta do sistema?')" 
               class="w-full bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold py-3 px-6 rounded-xl transition text-sm flex items-center justify-center gap-2 border border-rose-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Excluir Minha Conta
            </a>
        </div>

    </div>

</body>
</html>