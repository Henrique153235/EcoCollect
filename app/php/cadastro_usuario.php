<?php
require_once __DIR__ . '/init.php';

$Usuario = [];
$erro = "";

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    try {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $dados = read($pdo, "Usuarios", "id = ?", [$id]);
            $Usuario = $dados ?: [];
        }
    } catch (Exception $e) {
        $erro = "Erro ao buscar usuário: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telefone = trim($_POST['telefone'] ?? '');

    if (!empty($nome) && !empty($senha) && $email && !empty($telefone)) {
        try {
            $novoUsuario = [
                'nome'     => $nome,
                'senha'    => password_hash($senha, PASSWORD_DEFAULT),
                'email'    => $email,
                'telefone' => $telefone,
            ];

            create($pdo, "Usuarios", $novoUsuario);
            header("Location: login_usuario.php");
            exit;
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar usuário (verifique se o e-mail já existe): " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos corretamente.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuário | EcoCollect</title>
    <link rel="stylesheet" href="../css/cadastro_usuario.css">
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
        <h2 class="box-titulo"><?= isset($_GET['id']) ? 'Editar Usuário' : 'Cadastrar Usuário' ?></h2>
        
        <?php if (!empty($erro)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-option">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" maxlength="100" value="<?= htmlspecialchars($Usuario['nome'] ?? '') ?>" required>
            </div>
            
            <div class="form-option">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            
            <div class="form-option">
                <label for="telefone">Telefone:</label>
                <input type="tel" name="telefone" id="telefone" maxlength="14" value="<?= htmlspecialchars($Usuario['telefone'] ?? '') ?>" required>
            </div>
            
            <div class="form-option">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" maxlength="100" value="<?= htmlspecialchars($Usuario['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar">Confirmar</button>
            </div>
        </form>

        <p class="txt-login">Já possui uma conta?<a href="login_usuario.php"> Entre aqui.</a></p>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>
</body>
</html>