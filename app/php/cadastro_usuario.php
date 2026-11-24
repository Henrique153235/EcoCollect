<?php

declare(strict_types=1);

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
    <title>EcoCollect | Cadastro de Usuário</title>
    <link rel="stylesheet" href="../css/cadastro_usuario.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>
    <main>
        <section class="caixa-cadastro">
            <h2><?= isset($_GET['id']) ? 'Editar Usuário' : 'Cadastrar Usuário' ?></h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
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
                    <button type="submit" class="btn-confirmar">Confirmar</button>
                </div>
            </form>

            <p class="txt-login">Já possui uma conta? <a href="login_usuario.php">Entre aqui.</a></p>
        </section>
    </main>

    <?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>