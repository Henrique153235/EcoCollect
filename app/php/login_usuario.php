<?php

require_once __DIR__ . '/init.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nomeInput  = trim($_POST['nome'] ?? '');
    $senhaInput = trim($_POST['senha'] ?? '');

    if (empty($nomeInput) || empty($senhaInput)) {
        $erro = "Preencha todos os campos!";
    } else {
        try {
            $usuarioEncontrado = read($pdo, "Usuarios", "nome = ?", [$nomeInput]);

            if (
                $usuarioEncontrado &&
                (password_verify($senhaInput, $usuarioEncontrado['senha']) || $usuarioEncontrado['senha'] === $senhaInput)
            ) {
                $_SESSION['usuario_id']     = $usuarioEncontrado['id'];
                $_SESSION['usuario_nome']   = $usuarioEncontrado['nome'];

                header("Location: dashboard.php");
                exit;
            } else {
                $erro = "Usuário ou senha incorretos!";
            }
        } catch (Exception $e) {
            $erro = "Erro de conexão/banco: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login | EcoCollect</title>
    <link rel="stylesheet" href="../css/login_usuario.css">
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
        <h2 class="box-titulo">Login do Sistema</h2>

        <?php if (!empty($erro)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="" method="POST" autocomplete="off">
            <div class="form-option">
                <label for="nome">Usuário:</label>
                <input type="text" id="nome" name="nome" autocomplete="off" required>
            </div>

            <div class="form-option">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" autocomplete="new-password" required>
            </div>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar">Entrar</button>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>