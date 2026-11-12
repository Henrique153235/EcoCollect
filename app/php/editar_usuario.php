<?php

require_once __DIR__ . '/init.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login_usuario.php");
    exit;
}

$idUsuarioLogado = (int) $_SESSION['usuario_id'];

$usuario = read($pdo, "Usuarios", "id = ?", [$idUsuarioLogado]);

if (!$usuario) {
    header("Location: login_usuario.php");
    exit;
}

$mensagemSucesso = $_SESSION['flash_sucesso'] ?? "";
$mensagemErro = $_SESSION['flash_erro'] ?? "";
unset($_SESSION['flash_sucesso'], $_SESSION['flash_erro']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'atualizar_perfil') {
        $nome = trim($_POST['nome'] ?? '');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $telefone = trim($_POST['telefone'] ?? '');
        $novaSenha = $_POST['nova_senha'] ?? '';

        if (!empty($nome) && $email && !empty($telefone)) {
            try {
                // Verifica se o e-mail já pertence a outro usuário
                $emailEmUso = read($pdo, "Usuarios", "email = ? AND id != ?", [$email, $idUsuarioLogado]);

                if ($emailEmUso) {
                    $mensagemErro = "Este e-mail já está em uso por outra conta.";
                } else {
                    $dadosAtualizados = [
                        'nome'     => $nome,
                        'email'    => $email,
                        'telefone' => $telefone,
                    ];

                    if (!empty($novaSenha)) {
                        $dadosAtualizados['senha'] = password_hash($novaSenha, PASSWORD_DEFAULT);
                    }

                    update($pdo, "Usuarios", $dadosAtualizados, "id = ?", [$idUsuarioLogado]);

                    $_SESSION['usuario_nome'] = $nome;
                    $_SESSION['flash_sucesso'] = "Informações do perfil atualizadas com sucesso!";
                    header("Location: editarPerfil.php");
                    exit;
                }
            } catch (PDOException $e) {
                $mensagemErro = "Erro ao atualizar perfil: " . $e->getMessage();
            }
        } else {
            $mensagemErro = "Preencha nome, e-mail válido e telefone corretamente.";
        }
    }

    if ($action === 'excluir_perfil') {
        try {
            $pdo->beginTransaction();

            // Remove as coletas vinculadas ao usuário antes de apagar o registro
            delete($pdo, "Coleta", "idUsuario = ?", [$idUsuarioLogado]);
            delete($pdo, "Usuarios", "id = ?", [$idUsuarioLogado]);

            $pdo->commit();

            session_unset();
            session_destroy();

            header("Location: login_usuario.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensagemErro = "Não foi possível excluir o perfil: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | EcoCollect</title>
    <link rel="stylesheet" href="../css/editar_usuario.css">
</head>

<body>

    <header>
        <div class="nome-projeto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav>
            <a href="index.php">Início</a>
            <a href="dashboard.php">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="cadastro_usuario.php">Usuário</a>
                    <a href="cadastro_ponto_coleta.php">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Perfil</summary>
                <div class="sub-menu menu-direita">
                    <a href="editar_usuario.php" class="active">Editar Perfil</a>
                    <a href="logout.php">Sair</a>
                </div>
            </details>
        </nav>
    </header>

    <main class="dashboard-main">
        <div class="perfil-container">

            <?php if (!empty($mensagemSucesso)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($mensagemErro) ?></div>
            <?php endif; ?>

            <section class="card-box">
                <h3 class="box-subtitulo">Editar Informações do Perfil</h3>
                <form method="POST" action="editarPerfil.php" class="form-coleta">
                    <input type="hidden" name="action" value="atualizar_perfil">

                    <div class="form-group">
                        <label for="nome">Nome Completo:</label>
                        <input type="text" id="nome" name="nome" maxlength="100" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" maxlength="14" value="<?= htmlspecialchars($usuario['telefone']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha (deixe em branco para manter a atual):</label>
                        <input type="password" id="nova_senha" name="nova_senha" placeholder="••••••••">
                    </div>

                    <button type="submit" class="btn-primary">Salvar Alterações</button>
                </form>
            </section>

            <section class="card-box danger-zone">
                <h3 class="box-subtitulo">Excluir Conta</h3>
                <p>Ao excluir seu perfil, todos os seus dados cadastrais, histórico de coletas e pontos acumulados serão removidos permanentemente. Esta ação não pode ser desfeita.</p>
                <form method="POST" action="editarPerfil.php" onsubmit="return confirm('Tem certeza que deseja excluir permanentemente seu perfil e todo o seu histórico de coletas?');">
                    <input type="hidden" name="action" value="excluir_perfil">
                    <button type="submit" class="btn-danger">Excluir Meu Perfil</button>
                </form>
            </section>

        </div>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>