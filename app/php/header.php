<?php

declare(strict_types=1);

$paginaAtual = $paginaAtual ?? basename($_SERVER['PHP_SELF'] ?? '');
$nomeUsuarioHeader = '';

if (isset($_SESSION['usuario_id'])) {
    $idUsuarioHeader = (int) $_SESSION['usuario_id'];
    if ($idUsuarioHeader > 0) {
        if (!empty($_SESSION['usuario_nome'])) {
            $nomeUsuarioHeader = trim((string) $_SESSION['usuario_nome']);
        } elseif (isset($pdo)) {
            try {
                $usuarioHeader = read($pdo, 'Usuarios', 'id = ?', [$idUsuarioHeader]);
                if ($usuarioHeader && !empty($usuarioHeader['nome'])) {
                    $nomeUsuarioHeader = trim((string) $usuarioHeader['nome']);
                    $_SESSION['usuario_nome'] = $nomeUsuarioHeader;
                }
            } catch (Throwable $e) {
                // Ignora falha silenciosamente para não quebrar a navegação
            }
        }
    }
}

$paginasCadastro = ['cadastro_usuario.php', 'cadastro_ponto_coleta.php', 'cadastro_campanha.php'];
$paginasColetas  = ['campanhas.php', 'visualizacao_ponto.php'];
?>
<header class="header">
    <a href="index.php" class="logo">
        <img src="../img/logo-nav.svg" alt="Logo EcoCollect">
        <div class="logo-texto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>
    </a>

    <nav class="navbar">
        <a href="index.php" class="<?= $paginaAtual === 'index.php' ? 'active' : '' ?>">Início</a>
        <a href="dashboard.php" class="<?= $paginaAtual === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="loja.php" class="<?= $paginaAtual === 'loja.php' ? 'active' : '' ?>">Loja</a>

        <div class="menu-item <?= in_array($paginaAtual, $paginasCadastro, true) ? 'active' : '' ?>">
            <span>Cadastro</span>
            <div class="sub-menu">
                <a href="cadastro_usuario.php" class="<?= $paginaAtual === 'cadastro_usuario.php' ? 'active' : '' ?>">Usuário</a>
                <a href="cadastro_ponto_coleta.php" class="<?= $paginaAtual === 'cadastro_ponto_coleta.php' ? 'active' : '' ?>">Ponto de Coleta</a>
                <a href="cadastro_campanha.php" class="<?= $paginaAtual === 'cadastro_campanha.php' ? 'active' : '' ?>">Campanha</a>
            </div>
        </div>

        <div class="menu-item <?= in_array($paginaAtual, $paginasColetas, true) ? 'active' : '' ?>">
            <span>Coletas</span>
            <div class="sub-menu menu-direita">
                <a href="">Orientações de Descarte</a>
                <a href="campanhas.php" class="<?= $paginaAtual === 'campanhas.php' ? 'active' : '' ?>">Campanhas de Descarte</a>
                <a href="visualizacao_ponto.php" class="<?= $paginaAtual === 'visualizacao_ponto.php' ? 'active' : '' ?>">Pontos de Coleta Próximos</a>
            </div>
        </div>

        <?php if ($nomeUsuarioHeader !== ''): ?>
            <div class="menu-item usuario-logado <?= $paginaAtual === 'editar_usuario.php' ? 'active' : '' ?>">
                <span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 4a3.5 3.5 0 1 1-3.5 3.5A3.5 3.5 0 0 1 12 6Zm0 14a7.93 7.93 0 0 1-5.65-2.34 4.5 4.5 0 0 1 11.3 0A7.93 7.93 0 0 1 12 20Z"/>
                    </svg>
                    <?= htmlspecialchars($nomeUsuarioHeader) ?>
                </span>
                <div class="sub-menu menu-direita">
                    <a href="editar_usuario.php" class="<?= $paginaAtual === 'editar_usuario.php' ? 'active' : '' ?>">Editar Perfil</a>
                    <a href="logout.php">Sair</a>
                </div>
            </div>
        <?php endif; ?>
    </nav>
</header>