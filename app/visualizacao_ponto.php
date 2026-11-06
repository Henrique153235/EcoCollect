<?php

require_once __DIR__ . "/banco/crud.php";
require_once __DIR__ . "/banco/database.php";

$pontoColeta = readAll(database(), "ViewPontosDeColeta");

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TESTE - PONTOS DE COLETA</title>
    <link rel="stylesheet" href="visualizacao_ponto.css">
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

        <section class="dashboard-container">
            <div class="dashboard-header">
                <h2 class="box-titulo">Pontos de Coleta Cadastrados</h2>
            </div>

            <div class="grid-cards">
                <?php foreach ($pontoColeta as $p): ?>
                    <article class="card-ponto">
                        <div class="card-header">
                            <span class="card-id"><strong>Ponto:</strong> #<?= htmlspecialchars($p['id']) ?></span>
                        </div>

                        <div class="card-body">
                            <p class="card-info">
                            <?php $cep_formatado = substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5); ?>
                                <strong>CEP:</strong> <?= htmlspecialchars($cep_formatado) ?>
                            </p>
                            <p class="card-status">
                                <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?>
                            </p>
                            <p class="card-info">
                                <strong>Materiais Aceitos:</strong>
                                <span class="materiais-lista"><?= htmlspecialchars($p['materiais']) ?></span>
                            </p>
                        </div>

                        <div class="card-actions">
                            <a href="edicao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-editar">Editar</a>
                            <a href="remocao_ponto_coleta.php?id=<?= $p['id'] ?>" class="btn-acao btn-remover">Remover</a>
                        </div>
                    </article>
                <?php endforeach; ?>
                <a href="cadastro_ponto_coleta.php" class="btn-cadastrar botao-link">Cadastrar Novo Ponto</a>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy;2026 Ambientify. Todos os direitos reservados</p>
    </footer>

</body>

</html>