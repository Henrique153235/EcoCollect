<?php

require_once __DIR__ . '/init.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: visualizacao_ponto.php?erro=id_invalido");
    exit;
}

$pontosColeta = read($pdo, "ViewPontosDeColeta", "id = ?", [$id]);

if (!$pontosColeta) {
    header("Location: visualizacao_ponto.php?erro=nao_encontrado");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        delete($pdo, "PontosDeColeta", "id = ?", [$id]);
        header("Location: visualizacao_ponto.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Não foi possível excluir este ponto porque já existem coletas registradas nele. Caso não esteja mais ativo, altere o status para Indisponível na edição.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Ponto de Coleta | EcoCollect</title>
    <link rel="stylesheet" href="../css/remocao_ponto_coleta.css">
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
        <h2 class="box-titulo">Excluir Ponto de Coleta</h2>

        <?php if (!empty($erro)): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php else: ?>
            <p class="mensagem-alerta">Tem certeza que deseja remover este ponto de coleta?</p>
        <?php endif; ?>

        <form action="remocao_ponto_coleta.php?id=<?= (int) $id ?>" method="post">
            <ul class="lista-detalhes">
                <li><strong>ID:</strong> #<?= htmlspecialchars((string) ($pontosColeta['id'] ?? '')) ?></li>
                <li>
                    <?php
                    $cep = $pontosColeta['cep'] ?? '';
                    $cep_formatado = (strlen($cep) == 8) ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
                    ?>
                    <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                </li>
                <li><strong>Status:</strong> <?= htmlspecialchars(ucfirst($pontosColeta['status'] ?? '')) ?></li>
                <li><strong>Materiais:</strong> <?= htmlspecialchars($pontosColeta['materiais'] ?? '') ?></li>
            </ul>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar-exclusao">Confirmar Exclusão</button>
                <a href="visualizacao_ponto.php" class="btn-acao btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>