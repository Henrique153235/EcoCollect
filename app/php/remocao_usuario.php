<?php

require_once __DIR__ . '/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: cadastroUsuario.php?erro=id_invalido");
    exit;
}

$Usuarios = read($pdo, "Usuarios", "id = ?", [$id]);

if (!$Usuarios) {
    header("Location: cadastroUsuario.php?erro=nao_encontrado");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        delete($pdo, "Coleta", "idUsuario = ?", [$id]);
        delete($pdo, "Usuarios", "id = ?", [$id]);

        $pdo->commit();

        if (isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] == $id) {
            session_unset();
            session_destroy();
            header("Location: login_usuario.php");
            exit;
        }

        header("Location: cadastroUsuario.php");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $erro = "Erro ao excluir usuário: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deletar Usuário | EcoCollect</title>
    <link rel="stylesheet" href="../css/remocao_usuario.css">
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
        </nav>
    </header>

    <main>
        <h2 class="box-titulo">Excluir Usuário</h2>

        <?php if (!empty($erro)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php else: ?>
            <p class="mensagem-alerta">Tem certeza que deseja remover este Usuário?</p>
        <?php endif; ?>

        <form action="FormDeletaUsuario.php?id=<?= (int) $id ?>" method="post">
            <ul class="lista-detalhes">
                <li><strong>ID:</strong> #<?= htmlspecialchars((string) ($Usuarios['id'] ?? '')) ?></li>
                <li><strong>Nome:</strong> <?= htmlspecialchars($Usuarios['nome'] ?? '') ?></li>
                <li><strong>E-mail:</strong> <?= htmlspecialchars($Usuarios['email'] ?? '') ?></li>
                <li><strong>Telefone:</strong> <?= htmlspecialchars($Usuarios['telefone'] ?? '') ?></li>
            </ul>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar-exclusao">Confirmar Exclusão</button>
                <a href="dashboard.php" class="btn-acao btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>