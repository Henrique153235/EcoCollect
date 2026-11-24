<?php

declare(strict_types=1);

require_once __DIR__ . '/init.php';

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emailInput = trim($_POST['email'] ?? '');
    $senhaInput = trim($_POST['senha'] ?? '');

    if (empty($emailInput) || empty($senhaInput)) {
        $erro = "Preencha todos os campos!";
    } else {
        try {
            $usuarioEncontrado = read($pdo, "Usuarios", "email = ?", [$emailInput]);

            if (
                $usuarioEncontrado &&
                (password_verify($senhaInput, $usuarioEncontrado['senha']) || $usuarioEncontrado['senha'] == $senhaInput)
            ) {
                $_SESSION['usuario_id']   = $usuarioEncontrado['id'];
                $_SESSION['usuario_nome'] = $usuarioEncontrado['nome'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoCollect | Login</title>
    <link rel="stylesheet" href="../css/login_usuario.css">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="icon" type="image/png" href="../img/img-logo.jpg">
</head>

<body>

    <?php require_once __DIR__ . "/header.php" ?>

    <main>
        <section class="caixa-login">
            <h2>Login do Sistema</h2>

            <?php if (!empty($erro)): ?>
                <p class="msg-erro"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>

            <form action="" method="POST" autocomplete="off">
                <div class="form-option">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" autocomplete="off" required>
                </div>

                <div class="form-option">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" autocomplete="new-password" required>
                </div>

                <div class="form-btns">
                    <button type="submit" class="btn-entrar">Entrar</button>
                </div>
            </form>

            <p class="txt-cadastro">Ainda não tem um cadastro? <a href="cadastro_usuario.php">Cadastre-se aqui.</a></p>
        </section>
    </main>

    <?php require_once __DIR__ . "/footer.php" ?>

</body>

</html>