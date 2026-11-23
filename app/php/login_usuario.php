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

    <footer class="box-footer">
        <div class="content-footer">
            <div class="footer-col">
                <h2>EcoCollect</h2>
                <span>by Ambientify</span>
                <p>Conectando pessoas e pontos de coleta seletiva para transformar o descarte consciente em impacto ambiental positivo.</p>
            </div>

            <div class="footer-col">
                <h3>Orientações</h3>
                <a href="visualizacao_ponto.php">Pontos de Coleta Próximos</a>
                <a href="">Orientações de Descarte</a>
                <a href="">Campanhas de Descarte</a>
            </div>

            <div class="footer-col">
                <h3>Instituições</h3>
                <a href="https://cempre.org.br/" target="_blank" rel="noopener noreferrer">CEMPRE - Compromisso Empresarial para Reciclagem</a>
                <a href="https://www.reciclasampa.com.br/" target="_blank" rel="noopener noreferrer">Recicla Sampa</a>
                <a href="https://ambipar.com/" target="_blank" rel="noopener noreferrer">Ambipar Group</a>
                <a href="https://www.eureciclo.com.br/" target="_blank" rel="noopener noreferrer">eureciclo</a>
            </div>

            <div class="footer-col">
                <h3>Contato</h3>
                <p>contato@ecocollect.com.br</p>
                <p>Suporte Ambientify</p>
                <p>Segunda a Sexta, 08h às 18h</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 EcoCollect by Ambientify. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>