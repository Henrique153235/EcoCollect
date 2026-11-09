<?php

require_once __DIR__ . "/banco/crud.php";
require_once __DIR__ . "/banco/database.php";

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['id'])) {
    $pontosColeta = read(database(), "ViewPontosDeColeta", "id = {$_GET['id']}");
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    delete(database(), "PontosDeColeta", "id = {$_GET['id']}");
    header("Location: visualizacao_ponto.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir um Ponto</title>
    <link rel="stylesheet" href="remocao_ponto_coleta.css">
</head>

<body>
    <header>
        <div class="nome-projeto">
            <h1>EcoCollect</h1>
            <h3>by Ambientify</h3>
        </div>

        <nav>
            <a href="index.html">Início</a>
            <a href="dashboard.html">Dashboard</a>

            <details name="menu-nav">
                <summary>Cadastro</summary>
                <div class="sub-menu">
                    <a href="">Usuário</a>
                    <a href="">Ponto de Coleta</a>
                    <a href="">Campanha</a>
                </div>
            </details>

            <details name="menu-nav">
                <summary>Coletas</summary>
                <div class="sub-menu menu-direita">
                    <a href="">Orientações de Descarte</a>
                    <a href="">Campanhas de Descarte</a>
                    <a href="">Pontos de Coleta Próximos</a>
                </div>
            </details>
        </nav>
    </header>

    <main>
        <h2 class="box-titulo">Excluir Ponto de Coleta</h2>

        <p class="mensagem-alerta">Tem certeza que deseja remover este ponto de coleta?</p>

        <form action="" method="post">
            <ul class="lista-detalhes">
                <li><strong>ID:</strong> #<?= htmlspecialchars($pontosColeta['id'] ?? '') ?></li>
                <li>
                    <?php
                    $cep = $pontosColeta['cep'] ?? '';
                    $cep_formatado = (strlen($cep) === 8) ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
                    ?>
                    <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                </li>
                <li><strong>Status:</strong> <?= htmlspecialchars($pontosColeta['status'] ?? '') ?></li>
                <li><strong>Materiais:</strong> <?= htmlspecialchars($pontosColeta['materiais'] ?? '') ?></li>
            </ul>

            <div class="form-btns">
                <button type="submit" class="btn-acao btn-confirmar-exclusao">Confirmar Exclusão</button>
                <a href="./visualizacao_ponto.php" class="btn-acao btn-cancelar">Cancelar</a>
            </div>
        </form>
    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>